<?php

namespace App\Http\Controllers;

use App\Models\GuardDutyShift;
use App\Services\GuardDutyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminGuardDutyController extends Controller
{
    public function __construct(protected GuardDutyService $guardDutyService)
    {
    }

    public function index(): View
    {
        return view('admin.guard-duty');
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $filters = $this->validateFilters($request);
            $paginator = $this->guardDutyService
                ->historyQuery($filters)
                ->paginate($filters['per_page'])
                ->withQueryString();

            $rows = $paginator->getCollection()
                ->map(fn (GuardDutyShift $shift) => $this->guardDutyService->serializeAdminShift($shift))
                ->values();

            return response()->json([
                'success' => true,
                'current' => $this->guardDutyService->currentDutyShifts(),
                'last_completed' => $this->guardDutyService->lastCompletedShift(),
                'history' => [
                    'data' => $rows,
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => max(1, $paginator->lastPage()),
                        'per_page' => $paginator->perPage(),
                        'from' => $paginator->firstItem() ?? 0,
                        'to' => $paginator->lastItem() ?? 0,
                        'total' => $paginator->total(),
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load guard duty records. Please try again.',
            ], 500);
        }
    }

    public function filters(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'stations' => $this->guardDutyService->stationOptions(),
                    'statuses' => [
                        ['id' => '', 'label' => 'All'],
                        ['id' => 'on_duty', 'label' => 'On Duty'],
                        ['id' => 'completed', 'label' => 'Completed'],
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load guard duty filters. Please try again.',
            ], 500);
        }
    }

    public function show(int $shift): JsonResponse
    {
        try {
            $record = $this->findShift($shift);

            if (! $record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guard duty shift not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->guardDutyService->serializeAdminShift($record),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load guard duty details. Please try again.',
            ], 500);
        }
    }

    public function visitors(Request $request, int $shift): JsonResponse
    {
        try {
            $record = $this->findShift($shift);

            if (! $record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guard duty shift not found.',
                ], 404);
            }

            $perPage = $this->resolvePerPage($request);
            $paginator = $this->guardDutyService->paginateShiftVisits($record, $perPage);

            return response()->json([
                'success' => true,
                'shift' => $this->guardDutyService->serializeAdminShift($record),
                'data' => $paginator->getCollection()->values(),
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
                'message' => 'Unable to load visitors for this shift. Please try again.',
            ], 500);
        }
    }

    protected function findShift(int $shiftId): ?GuardDutyShift
    {
        return GuardDutyShift::query()
            ->with(['guardUser', 'guardProfile'])
            ->withCount('visits')
            ->find($shiftId);
    }

    /**
     * @return array{
     *     page: int,
     *     per_page: int,
     *     search: string,
     *     station: string,
     *     status: string,
     *     date_range: string,
     *     date_from: ?string,
     *     date_to: ?string
     * }
     */
    protected function validateFilters(Request $request): array
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:5,10,25,50,75,100'],
            'search' => ['nullable', 'string', 'max:255'],
            'station' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
            'date_range' => ['nullable', 'string', 'in:today,yesterday,last_7_days,last_30_days,custom,all'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        return [
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 5),
            'search' => trim((string) ($validated['search'] ?? '')),
            'station' => trim((string) ($validated['station'] ?? '')),
            'status' => trim((string) ($validated['status'] ?? '')),
            'date_range' => trim((string) ($validated['date_range'] ?? 'all')),
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
        ];
    }
}
