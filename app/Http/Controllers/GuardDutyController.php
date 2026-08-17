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

    public function assign(Request $request): JsonResponse
    {
        $credentials = $this->validatedCredentials($request);

        try {
            $payload = $this->guardDutyService->assignGuard(
                $credentials['email'],
                $credentials['password'],
                $this->kioskUserId($request),
                $request->ip()
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
        $credentials = $this->validatedCredentials($request, true);

        try {
            $payload = $this->guardDutyService->changeGuard(
                $credentials['email'],
                $credentials['password'],
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
        $credentials = $this->validatedEndDutyPassword($request);

        try {
            $payload = $this->guardDutyService->endDuty(
                $credentials['password'],
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
     * @return array{email: string, password: string}
     */
    protected function validatedCredentials(Request $request, bool $changing = false): array
    {
        $emailLabel = $changing ? 'New Guard Email' : 'Email / Guard Account';

        return $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'email.required' => $emailLabel.' is required.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);
    }

    /**
     * @return array{password: string}
     */
    protected function validatedEndDutyPassword(Request $request): array
    {
        return $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ], [
            'password.required' => 'Password is required.',
        ]);
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
