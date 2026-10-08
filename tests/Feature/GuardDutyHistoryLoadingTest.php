<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminGuardDutyController;
use App\Services\GuardDutyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class GuardDutyHistoryLoadingTest extends TestCase
{
    public function test_summary_requires_an_authenticated_administrator(): void
    {
        $route = Route::getRoutes()->getByName('api.admin.guard-duty.summary');
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('role:1', $route->gatherMiddleware());
        $this->getJson(route('api.admin.guard-duty.summary'))->assertUnauthorized();
    }

    public function test_history_only_request_skips_current_and_last_completed_queries(): void
    {
        $service = Mockery::mock(GuardDutyService::class);
        $builder = Mockery::mock(Builder::class);
        $service->shouldReceive('historyQuery')->once()->with(Mockery::on(
            fn ($filters) => $filters['search'] === 'Juan' && $filters['status'] === 'completed'
        ))->andReturn($builder);
        $builder->shouldReceive('paginate')->once()->with(5)->andReturn(new LengthAwarePaginator([], 12, 5, 2));
        $service->shouldNotReceive('currentDutyShifts');
        $service->shouldNotReceive('lastCompletedShift');

        $request = Request::create('/api/admin/guard-duty', 'GET', [
            'include_summary' => '0', 'page' => 2, 'search' => 'Juan', 'status' => 'completed',
        ]);
        $payload = (new AdminGuardDutyController($service))->list($request)->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame(12, $payload['history']['meta']['total']);
        $this->assertSame(2, $payload['history']['meta']['current_page']);
        $this->assertSame(3, $payload['history']['meta']['last_page']);
        $this->assertArrayNotHasKey('current', $payload);
        $this->assertArrayNotHasKey('last_completed', $payload);
    }

    public function test_existing_list_clients_still_receive_the_summary_by_default(): void
    {
        $service = Mockery::mock(GuardDutyService::class);
        $builder = Mockery::mock(Builder::class);
        $service->shouldReceive('historyQuery')->once()->andReturn($builder);
        $builder->shouldReceive('paginate')->once()->with(5)->andReturn(new LengthAwarePaginator([], 0, 5));
        $service->shouldReceive('currentDutyShifts')->once()->andReturn(collect());
        $service->shouldReceive('lastCompletedShift')->once()->andReturn(['shift_id' => 9]);

        $payload = (new AdminGuardDutyController($service))->list(Request::create('/api/admin/guard-duty'))->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame([], $payload['current']);
        $this->assertSame(9, $payload['last_completed']['shift_id']);
    }

    public function test_summary_skips_last_completed_lookup_when_a_guard_is_on_duty(): void
    {
        $service = Mockery::mock(GuardDutyService::class);
        $service->shouldReceive('currentDutyShifts')->once()->andReturn(collect([['shift_id' => 10]]));
        $service->shouldNotReceive('lastCompletedShift');

        $payload = (new AdminGuardDutyController($service))->summary()->getData(true);

        $this->assertSame(10, $payload['current'][0]['shift_id']);
        $this->assertNull($payload['last_completed']);
    }

    public function test_summary_shows_last_completed_duty_when_no_guard_is_active(): void
    {
        $service = Mockery::mock(GuardDutyService::class);
        $service->shouldReceive('currentDutyShifts')->once()->andReturn(collect());
        $service->shouldReceive('lastCompletedShift')->once()->andReturn(['shift_id' => 9]);

        $payload = (new AdminGuardDutyController($service))->summary()->getData(true);

        $this->assertSame([], $payload['current']);
        $this->assertSame(9, $payload['last_completed']['shift_id']);
    }
}
