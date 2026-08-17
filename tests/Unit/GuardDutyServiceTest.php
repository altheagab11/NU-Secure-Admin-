<?php

namespace Tests\Unit;

use App\Exceptions\GuardDutyUnavailableException;
use App\Http\Controllers\GuardDutyController;
use App\Models\GuardDutyShift;
use App\Models\User;
use App\Models\Visit;
use App\Services\GuardDutyService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuardDutyServiceTest extends TestCase
{
    #[Test]
    public function it_formats_shift_durations_consistently(): void
    {
        $this->assertSame('0m', GuardDutyService::formatDurationMinutes(0));
        $this->assertSame('42m', GuardDutyService::formatDurationMinutes(42));
        $this->assertSame('3h 42m', GuardDutyService::formatDurationMinutes((3 * 60) + 42));
        $this->assertSame('8h', GuardDutyService::formatDurationMinutes(8 * 60));
        $this->assertSame('1d 2h 5m', GuardDutyService::formatDurationMinutes((26 * 60) + 5));
    }

    #[Test]
    public function duty_shift_and_visit_relationships_are_defined(): void
    {
        $shift = new GuardDutyShift;
        $visit = new Visit;
        $user = new User;

        $this->assertInstanceOf(HasMany::class, $shift->visits());
        $this->assertSame('duty_shift_id', $shift->visits()->getForeignKeyName());
        $this->assertInstanceOf(BelongsTo::class, $visit->dutyShift());
        $this->assertInstanceOf(BelongsTo::class, $visit->onDutyGuard());
        $this->assertInstanceOf(HasMany::class, $user->dutyShifts());
    }

    #[Test]
    public function missing_shift_and_invalid_credentials_messages_are_generic(): void
    {
        $this->assertSame(
            'No active guard duty shift was found.',
            GuardDutyUnavailableException::missingShift()->getMessage()
        );
        $this->assertSame(
            'No security guard is currently assigned. Please contact the security desk.',
            GuardDutyUnavailableException::missing()->getMessage()
        );
        $this->assertSame('Invalid guard credentials.', GuardDutyService::INVALID_CREDENTIALS_MESSAGE);
        $this->assertSame('No active guard duty shift was found.', GuardDutyService::NO_ACTIVE_SHIFT_MESSAGE);
    }

    #[Test]
    public function change_guard_and_end_duty_are_separate_service_methods(): void
    {
        $this->assertTrue(method_exists(GuardDutyService::class, 'changeGuard'));
        $this->assertTrue(method_exists(GuardDutyService::class, 'endDuty'));
        $this->assertTrue(method_exists(GuardDutyService::class, 'assignGuard'));
        $this->assertTrue(method_exists(GuardDutyController::class, 'end'));
    }
}
