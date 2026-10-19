<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminOfficeDirectoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validateOffice($request, true);
        DB::transaction(function () use ($data) {
            $this->lockDirectoryNames();
            $this->ensureUniqueName($data['office_name']);
            $office = Office::create($data);
            $this->audit('Office Created', $office, null, $office->getAttributes());
        });

        return back()->with('directory_success', 'Office added successfully!');
    }

    public function update(Request $request, int $office)
    {
        $data = $this->validateOffice($request, false);
        DB::transaction(function () use ($office, $data) {
            $this->lockDirectoryNames();
            $record = Office::whereKey($office)->lockForUpdate()->firstOrFail();
            $this->ensureUniqueName($data['office_name'], $office);
            if ($record->is_active && ! $data['is_active']) {
                $this->ensureCanDeactivate($office);
            }
            $old = $record->getAttributes();
            $record->fill($data)->save();
            $this->audit('Office Updated', $record, $old, $record->getAttributes());
            if ((bool) $old['is_active'] !== $record->is_active) {
                $this->audit($record->is_active ? 'Office Reactivated' : 'Office Deactivated', $record, $old, $record->getAttributes());
            }
        });

        return back()->with('directory_success', 'Office updated successfully!');
    }

    public function status(Request $request, int $office)
    {
        $data = $request->validateWithBag('officeDirectory', ['is_active' => ['required', 'boolean']]);
        $active = (bool) $data['is_active'];
        DB::transaction(function () use ($office, $active) {
            $record = Office::whereKey($office)->lockForUpdate()->firstOrFail();
            if ($record->is_active === $active) {
                return;
            }
            if (! $active) {
                $this->ensureCanDeactivate($office);
            }
            $old = $record->getAttributes();
            $record->is_active = $active;
            $record->save();
            $this->audit($active ? 'Office Reactivated' : 'Office Deactivated', $record, $old, $record->getAttributes());
        });

        return back()->with('directory_success', $active ? 'Office reactivated successfully!' : 'Office deactivated successfully!');
    }

    private function validateOffice(Request $request, bool $creating): array
    {
        return $request->validateWithBag('officeDirectory', [
            'office_name' => ['required', 'string', 'max:150'],
            'room_number' => [$creating ? 'required' : 'nullable', 'string', 'max:30', 'regex:/\A[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*\z/'],
            'floor' => [$creating ? 'required' : 'nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ], ['room_number.regex' => 'Use letters, numbers, and hyphens for the room number (e.g. G-03 or 204A).']);
    }

    private function lockDirectoryNames(): void
    {
        // The existing database unique key is case-sensitive. Serialize directory
        // writes so concurrent case variants cannot pass the application check.
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select('select pg_advisory_xact_lock(?)', [8672041]);
        }
    }

    private function ensureUniqueName(string $name, ?int $except = null): void
    {
        $exists = Office::whereRaw('LOWER(TRIM(office_name)) = ?', [mb_strtolower(trim($name))])
            ->when($except !== null, fn ($query) => $query->where('office_id', '!=', $except))->exists();
        if ($exists) {
            throw ValidationException::withMessages(['office_name' => 'An office with this name already exists.'])->errorBag('officeDirectory');
        }
    }

    private function ensureCanDeactivate(int $officeId): void
    {
        $visits = DB::table('visit as v')->whereNull('v.exit_time')
            ->where(function ($query) use ($officeId) {
                $query->where('v.primary_office_id', $officeId)->orWhereExists(function ($route) use ($officeId) {
                    $route->selectRaw('1')->from('office_expectation as oe')->whereColumn('oe.visit_id', 'v.visit_id')->where('oe.office_id', $officeId);
                });
            })->count();
        $staff = DB::table('office_staff as s')->join('users as u', 'u.user_id', '=', 's.user_id')
            ->where('s.office_id', $officeId)->whereRaw("LOWER(TRIM(COALESCE(u.status, 'active'))) != ?", ['recycle_bin'])->count();
        if ($visits || $staff) {
            throw ValidationException::withMessages(['is_active' => "This office cannot be deactivated: {$visits} visit(s) are still inside and {$staff} office account(s) are assigned. Complete the visits and reassign or recycle the accounts first."])->errorBag('officeDirectory');
        }
    }

    private function audit(string $action, Office $office, ?array $old, array $new): void
    {
        $log = ActivityLogService::log(
            action: $action, module: 'Office Management',
            description: ActivityLogService::actorLabel().' '.strtolower($action).' '.$office->office_name.'.',
            entityType: 'Office', entityId: $office->office_id, oldValues: $old, newValues: $new
        );
        if (! $log) {
            throw new \RuntimeException('Unable to save the office audit record.');
        }
    }
}
