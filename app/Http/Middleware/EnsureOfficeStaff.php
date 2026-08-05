<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficeStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ((int) $user->role_id !== 3) {
            abort(403, 'You are not authorized to record visits for this office.');
        }

        $assignment = $this->resolveOfficeAssignment((int) $user->user_id);

        if (! $assignment || empty($assignment->office_id)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not assigned to an office. Contact an administrator.',
            ]);
        }

        if (isset($assignment->office_is_active) && ! (bool) $assignment->office_is_active) {
            abort(403, 'Your assigned office is currently inactive.');
        }

        $request->attributes->set('office_context', $assignment);
        view()->share('officeContext', $assignment);

        return $next($request);
    }

    protected function resolveOfficeAssignment(int $userId): ?object
    {
        if (! Schema::hasTable('office_staff')) {
            return null;
        }

        $query = DB::table('office_staff as s')
            ->leftJoin('office as o', 'o.office_id', '=', 's.office_id')
            ->leftJoin('users as u', 'u.user_id', '=', 's.user_id')
            ->where('s.user_id', $userId)
            ->select([
                's.staff_id',
                's.user_id',
                's.office_id',
                's.position',
                'o.office_name',
                'o.floor',
                'o.is_active as office_is_active',
                'u.first_name',
                'u.last_name',
                'u.email',
                'u.role_id',
                'u.status as user_status',
            ]);

        if (Schema::hasColumn('users', 'status')) {
            $query->where(function ($q) {
                $q->whereNull('u.status')->orWhere('u.status', '!=', 'recycle_bin');
            });
        }

        return $query->orderBy('s.staff_id')->first();
    }
}
