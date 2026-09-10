<?php

namespace App\Http\Controllers;

use App\Models\GuardPersonnel;
use App\Services\GuardPersonnelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AdminGuardPersonnelController extends Controller
{
    public function __construct(protected GuardPersonnelService $guardPersonnelService)
    {
    }

    public function index(): View
    {
        return view('admin.guard-personnel');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $filters = $this->validatedListFilters($request);
            $paginator = $this->guardPersonnelService->paginate($filters);

            return response()->json([
                'success' => true,
                'data' => $paginator->getCollection()
                    ->map(fn (GuardPersonnel $guard) => $this->guardPersonnelService->serialize($guard))
                    ->values(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => max(1, $paginator->lastPage()),
                    'per_page' => $paginator->perPage(),
                    'from' => $paginator->firstItem() ?? 0,
                    'to' => $paginator->lastItem() ?? 0,
                    'total' => $paginator->total(),
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load security guard personnel. Please try again.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $this->validatedStore($request);
            $guard = $this->guardPersonnelService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Guard personnel added.',
                'data' => $this->guardPersonnelService->serialize($guard),
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to add guard personnel right now. Please try again.',
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $guard = GuardPersonnel::query()->find($id);

        if (! $guard) {
            return response()->json([
                'success' => false,
                'message' => 'Guard personnel not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->guardPersonnelService->serialize($guard),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $guard = GuardPersonnel::query()->find($id);

        if (! $guard) {
            return response()->json([
                'success' => false,
                'message' => 'Guard personnel not found.',
            ], 404);
        }

        try {
            $data = $this->validatedUpdate($request, $guard);
            $updated = $this->guardPersonnelService->update($guard, $data);

            return response()->json([
                'success' => true,
                'message' => 'Guard personnel updated.',
                'data' => $this->guardPersonnelService->serialize($updated),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update guard personnel right now. Please try again.',
            ], 500);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $guard = GuardPersonnel::query()->find($id);

        if (! $guard) {
            return response()->json([
                'success' => false,
                'message' => 'Guard personnel not found.',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'status' => ['required', 'string', Rule::in([GuardPersonnel::STATUS_ACTIVE, GuardPersonnel::STATUS_INACTIVE])],
            ]);

            $updated = $this->guardPersonnelService->updateStatus($guard, $validated['status']);

            return response()->json([
                'success' => true,
                'message' => $updated->isActive() ? 'Guard activated.' : 'Guard deactivated.',
                'data' => $this->guardPersonnelService->serialize($updated),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update guard status right now. Please try again.',
            ], 500);
        }
    }

    public function updatePin(Request $request, int $id): JsonResponse
    {
        $guard = GuardPersonnel::query()->find($id);

        if (! $guard) {
            return response()->json([
                'success' => false,
                'message' => 'Guard personnel not found.',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'duty_pin' => ['required', 'digits:6'],
                'duty_pin_confirmation' => ['required', 'same:duty_pin'],
            ], [
                'duty_pin.required' => 'Duty PIN is required.',
                'duty_pin.digits' => 'Duty PIN must be exactly 6 digits.',
                'duty_pin_confirmation.required' => 'Please confirm the Duty PIN.',
                'duty_pin_confirmation.same' => 'Duty PIN confirmation does not match.',
            ]);

            $updated = $this->guardPersonnelService->updatePin($guard, $validated['duty_pin']);

            return response()->json([
                'success' => true,
                'message' => 'Duty PIN updated.',
                'data' => $this->guardPersonnelService->serialize($updated),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update the Duty PIN right now. Please try again.',
            ], 500);
        }
    }

    /**
     * @return array{page: int, per_page: int, search: string, status: string}
     */
    protected function validatedListFilters(Request $request): array
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:5,10,25,50'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        return [
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 10),
            'search' => trim((string) ($validated['search'] ?? '')),
            'status' => trim((string) ($validated['status'] ?? '')),
        ];
    }

    /**
     * @return array{
     *     first_name: string,
     *     middle_name: ?string,
     *     last_name: string,
     *     badge_number: string,
     *     duty_pin: string,
     *     status: string
     * }
     */
    protected function validatedStore(Request $request): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'badge_number' => ['required', 'string', 'max:50', 'unique:guard_personnel,badge_number'],
            'duty_pin' => ['required', 'digits:6'],
            'duty_pin_confirmation' => ['required', 'same:duty_pin'],
            'status' => ['nullable', 'string', Rule::in([GuardPersonnel::STATUS_ACTIVE, GuardPersonnel::STATUS_INACTIVE])],
        ], [
            'first_name.required' => 'First Name is required.',
            'last_name.required' => 'Last Name is required.',
            'badge_number.required' => 'Badge Number is required.',
            'badge_number.unique' => 'This badge number is already assigned to another guard.',
            'duty_pin.required' => 'Duty PIN is required.',
            'duty_pin.digits' => 'Duty PIN must be exactly 6 digits.',
            'duty_pin_confirmation.required' => 'Please confirm the Duty PIN.',
            'duty_pin_confirmation.same' => 'Duty PIN confirmation does not match.',
        ]);

        return [
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'badge_number' => $validated['badge_number'],
            'duty_pin' => $validated['duty_pin'],
            'status' => $validated['status'] ?? GuardPersonnel::STATUS_ACTIVE,
        ];
    }

    /**
     * @return array{
     *     first_name: string,
     *     middle_name: ?string,
     *     last_name: string,
     *     badge_number: string,
     *     status: string
     * }
     */
    protected function validatedUpdate(Request $request, GuardPersonnel $guard): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'badge_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('guard_personnel', 'badge_number')->ignore(
                    $guard->guard_personnel_id,
                    'guard_personnel_id'
                ),
            ],
            'status' => ['required', 'string', Rule::in([GuardPersonnel::STATUS_ACTIVE, GuardPersonnel::STATUS_INACTIVE])],
        ], [
            'first_name.required' => 'First Name is required.',
            'last_name.required' => 'Last Name is required.',
            'badge_number.required' => 'Badge Number is required.',
            'badge_number.unique' => 'This badge number is already assigned to another guard.',
        ]);

        return [
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'badge_number' => $validated['badge_number'],
            'status' => $validated['status'],
        ];
    }
}
