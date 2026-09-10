<?php

namespace App\Http\Controllers;

use App\Exceptions\GuardDutyUnavailableException;
use App\Services\GuardDutyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class GuardDutyController extends Controller
{
    public function __construct(protected GuardDutyService $guardDutyService)
    {
    }

    public function current(Request $request): JsonResponse
    {
        return response()->json($this->payload($request));
    }

    public function availableGuards(): JsonResponse
    {
        $guards = $this->guardDutyService->availableGuards();

        return response()->json([
            'success' => true,
            'data' => $guards,
            'message' => $guards === []
                ? GuardDutyService::NO_ACTIVE_PERSONNEL_MESSAGE
                : null,
        ]);
    }

    public function assign(Request $request): JsonResponse
    {
        $credentials = $this->validatedStartCredentials($request);

        try {
            $payload = $this->guardDutyService->assignGuard(
                $credentials['guard_personnel_id'],
                $credentials['duty_pin'],
                $this->kioskUserId($request),
                $request->ip(),
                $credentials['station']
            );
        } catch (ValidationException $e) {
            return $this->invalidCredentialsResponse($e);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to assign a guard on duty right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guard on duty assigned.',
            ...$payload,
        ]);
    }

    public function change(Request $request): JsonResponse
    {
        $credentials = $this->validatedStartCredentials($request);

        try {
            $payload = $this->guardDutyService->changeGuard(
                $credentials['guard_personnel_id'],
                $credentials['duty_pin'],
                $this->kioskUserId($request),
                $request->ip(),
                $credentials['station']
            );
        } catch (GuardDutyUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'has_active_guard' => false,
                'shift' => null,
            ], 422);
        } catch (ValidationException $e) {
            return $this->invalidCredentialsResponse($e);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to change the guard on duty right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guard on duty updated.',
            ...$payload,
        ]);
    }

    public function end(Request $request): JsonResponse
    {
        $credentials = $this->validatedEndDutyPin($request);

        try {
            $payload = $this->guardDutyService->endDuty(
                $credentials['duty_pin'],
                $this->kioskUserId($request),
                $request->ip()
            );
        } catch (GuardDutyUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'has_active_guard' => false,
                'shift' => null,
            ], 422);
        } catch (ValidationException $e) {
            return $this->invalidCredentialsResponse($e);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to end guard duty right now. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guard duty ended.',
            ...$payload,
        ]);
    }

    /**
     * @return array{guard_personnel_id: int, duty_pin: string, station: string}
     */
    protected function validatedStartCredentials(Request $request): array
    {
        $validated = $request->validate([
            'guard_personnel_id' => ['required', 'integer', 'min:1'],
            'duty_pin' => ['required', 'digits:6'],
            'station' => ['nullable', 'string', 'max:255'],
        ], [
            'guard_personnel_id.required' => 'Security Guard is required.',
            'duty_pin.required' => 'Duty PIN is required.',
            'duty_pin.digits' => 'Duty PIN must be exactly 6 digits.',
        ]);

        return [
            'guard_personnel_id' => (int) $validated['guard_personnel_id'],
            'duty_pin' => (string) $validated['duty_pin'],
            'station' => trim((string) ($validated['station'] ?? GuardDutyService::DEFAULT_STATION)) ?: GuardDutyService::DEFAULT_STATION,
        ];
    }

    /**
     * @return array{duty_pin: string}
     */
    protected function validatedEndDutyPin(Request $request): array
    {
        $validated = $request->validate([
            'duty_pin' => ['required', 'digits:6'],
        ], [
            'duty_pin.required' => 'Duty PIN is required.',
            'duty_pin.digits' => 'Duty PIN must be exactly 6 digits.',
        ]);

        return [
            'duty_pin' => (string) $validated['duty_pin'],
        ];
    }

    protected function kioskUserId(Request $request): int
    {
        return (int) $request->user()->user_id;
    }

    /**
     * @return array{has_active_guard: bool, shift: array<string, mixed>|null}
     */
    protected function payload(Request $request): array
    {
        $user = $request->user();
        $kioskUserId = (int) ($user->role_id ?? 0) === 4
            ? (int) $user->user_id
            : null;

        return $this->guardDutyService->payloadForKiosk($kioskUserId);
    }

    protected function invalidCredentialsResponse(ValidationException $e): JsonResponse
    {
        $message = collect($e->errors())->flatten()->first() ?: GuardDutyService::INVALID_CREDENTIALS_MESSAGE;
        $status = $e->status >= 400 ? $e->status : 422;

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
