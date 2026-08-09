<?php

namespace Tests\Unit;

use App\Services\DailyVisitorReportService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class DateRangeVisitorReportServiceTest extends TestCase
{
    #[Test]
    public function it_builds_per_day_complete_calendar_windows(): void
    {
        $service = new DailyVisitorReportService;
        $start = Carbon::parse('2026-08-01', 'Asia/Manila')->startOfDay();
        $end = Carbon::parse('2026-08-03', 'Asia/Manila')->startOfDay();

        $windows = $service->buildOperatingDayWindows($start, $end);

        $this->assertCount(3, $windows);
        $this->assertSame(['2026-08-01 00:00:00', '2026-08-01 23:59:59'], $windows[0]);
        $this->assertSame(['2026-08-02 00:00:00', '2026-08-02 23:59:59'], $windows[1]);
        $this->assertSame(['2026-08-03 00:00:00', '2026-08-03 23:59:59'], $windows[2]);

        foreach ($windows as [$windowStart, $windowEnd]) {
            $this->assertStringContainsString('00:00:00', $windowStart);
            $this->assertStringContainsString('23:59:59', $windowEnd);
            $this->assertStringNotContainsString('06:00:00', $windowStart);
        }
    }

    #[Test]
    public function it_rejects_invalid_date_ranges(): void
    {
        $service = new DailyVisitorReportService;
        config(['reports.max_range_days' => 31]);

        try {
            $service->normalizeDateRange('2026-07-03', '2026-07-01');
            $this->fail('Expected start-after-end rejection.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('on or after the start date', $e->getMessage());
        }

        try {
            $service->normalizeDateRange('2026-07-01', '2026-07-01');
            $this->fail('Expected one-day rejection.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('at least two days', $e->getMessage());
        }

        $future = Carbon::now('Asia/Manila')->addDay()->toDateString();
        $today = Carbon::now('Asia/Manila')->toDateString();

        try {
            $service->normalizeDateRange($today, $future);
            $this->fail('Expected future date rejection.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Future dates are not allowed', $e->getMessage());
        }

        try {
            $service->normalizeDateRange('2026-01-01', '2026-02-15');
            $this->fail('Expected max range rejection.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('cannot exceed 31 days', $e->getMessage());
        }
    }

    #[Test]
    public function it_accepts_two_day_and_multi_day_ranges(): void
    {
        $service = new DailyVisitorReportService;
        config(['reports.max_range_days' => 31]);

        [$start, $end] = $service->normalizeDateRange('2026-07-01', '2026-07-02');
        $this->assertSame('2026-07-01', $start->toDateString());
        $this->assertSame('2026-07-02', $end->toDateString());

        [$start2, $end2] = $service->normalizeDateRange('2026-07-01', '2026-07-31');
        $this->assertSame(31, (int) ($start2->diffInDays($end2) + 1));
    }

    #[Test]
    public function it_builds_expected_date_range_filename(): void
    {
        $service = new DailyVisitorReportService;
        $fileName = $service->buildDateRangeFileName('2026-08-01', '2026-08-03');

        $this->assertSame('NU-Secure_Visitor_Report_2026-08-01_to_2026-08-03.xlsx', $fileName);
        $this->assertSame(
            'reports/date-range/2026/08/NU-Secure_Visitor_Report_2026-08-01_to_2026-08-03.xlsx',
            $service->buildDateRangeRelativePath('2026-08-01', $fileName)
        );
    }

    #[Test]
    public function it_builds_empty_date_range_excel_with_metadata(): void
    {
        $service = new DailyVisitorReportService;
        $start = Carbon::parse('2026-08-01', 'Asia/Manila')->startOfDay();
        $end = Carbon::parse('2026-08-03', 'Asia/Manila')->startOfDay();
        $generatedAt = Carbon::parse('2026-08-03 18:00:00', 'Asia/Manila');

        $spreadsheet = $service->buildDateRangeSpreadsheet(
            $start,
            $end,
            collect([]),
            'System Admin',
            $generatedAt
        );

        $sheet = $spreadsheet->getActiveSheet();
        $this->assertSame('NU-Secure Visitor Date-Range Report', $sheet->getCell('A1')->getValue());
        $this->assertStringContainsString('August 1, 2026', (string) $sheet->getCell('A2')->getValue());
        $this->assertStringContainsString('August 3, 2026', (string) $sheet->getCell('A3')->getValue());
        $this->assertStringContainsString('Complete calendar days', (string) $sheet->getCell('A4')->getValue());
        $this->assertStringContainsString('12:00:00 AM to 11:59:59 PM', (string) $sheet->getCell('A4')->getValue());
        $this->assertStringContainsString('System Admin', (string) $sheet->getCell('A6')->getValue());
        $this->assertSame('Visit ID', $sheet->getCell('A8')->getValue());
        $this->assertSame('Control Number', $sheet->getCell('B8')->getValue());
        $this->assertSame('Total Visitors:', $sheet->getCell('A10')->getValue());
        $this->assertSame(0, (int) $sheet->getCell('B10')->getValue());
        $this->assertNotNull($sheet->getAutoFilter()->getRange());
        $this->assertSame('A9', $sheet->getFreezePane());
    }

    #[Test]
    public function it_preserves_leading_zeros_in_date_range_excel(): void
    {
        $service = new DailyVisitorReportService;
        $start = Carbon::parse('2026-08-01', 'Asia/Manila')->startOfDay();
        $end = Carbon::parse('2026-08-02', 'Asia/Manila')->startOfDay();

        $rows = new Collection([
            [
                'visit_id' => '7',
                'control_number' => '001234',
                'visitor_full_name' => 'Juan Dela Cruz',
                'birth_date' => Carbon::parse('1990-01-15', 'Asia/Manila'),
                'address' => 'Sample Address',
                'contact_number' => '09171234567',
                'pass_number' => '00098765',
                'visit_type' => 'Visitor',
                'purpose_of_visit' => 'Meeting',
                'office_or_person' => 'Registrar',
                'date_of_visit' => $start->copy(),
                'time_in' => $start->copy()->setTime(9, 30, 0),
                'time_out' => $start->copy()->setTime(11, 0, 0),
                'duration' => '1 hr 30 mins',
                'visit_status' => 'Completed',
                'entry_method' => 'Guard Registration',
                'processed_by' => 'Guard One',
                'alert' => 'None',
                'remarks' => '',
            ],
        ]);

        $spreadsheet = $service->buildDateRangeSpreadsheet($start, $end, $rows, 'Admin');
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B9')->getDataType());
        $this->assertSame('001234', $sheet->getCell('B9')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('F9')->getDataType());
        $this->assertSame('09171234567', $sheet->getCell('F9')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('G9')->getDataType());
        $this->assertSame('00098765', $sheet->getCell('G9')->getValue());
        $this->assertSame(1, (int) $sheet->getCell('B11')->getValue());
    }

    #[Test]
    public function date_range_workbook_can_be_saved_and_reopened(): void
    {
        $service = new DailyVisitorReportService;
        $start = Carbon::parse('2026-08-01', 'Asia/Manila')->startOfDay();
        $end = Carbon::parse('2026-08-02', 'Asia/Manila')->startOfDay();
        $spreadsheet = $service->buildDateRangeSpreadsheet($start, $end, collect([]), 'Admin');

        $temp = tempnam(sys_get_temp_dir(), 'date_range_report_').'.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($temp);

        $reloaded = IOFactory::load($temp);
        $this->assertSame(
            'NU-Secure Visitor Date-Range Report',
            $reloaded->getActiveSheet()->getCell('A1')->getValue()
        );

        @unlink($temp);
    }

    #[Test]
    public function daily_report_filename_format_remains_unchanged(): void
    {
        $service = new DailyVisitorReportService;
        $this->assertSame(
            'NU-Secure_Visitor_Report_2026-08-01.xlsx',
            $service->buildFileName('2026-08-01')
        );
    }
}
