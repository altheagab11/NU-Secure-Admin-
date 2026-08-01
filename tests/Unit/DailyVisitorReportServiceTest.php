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

class DailyVisitorReportServiceTest extends TestCase
{
    #[Test]
    public function it_builds_a_valid_excel_file_with_zero_visitors(): void
    {
        $service = new DailyVisitorReportService;
        $date = Carbon::parse('2026-08-01', 'Asia/Manila')->startOfDay();
        $spreadsheet = $service->buildSpreadsheet($date, collect([]));

        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('NU-Secure Daily Visitor Report', $sheet->getCell('A1')->getValue());
        $this->assertStringContainsString('August 1, 2026', (string) $sheet->getCell('A2')->getValue());
        $this->assertSame('Visit ID', $sheet->getCell('A4')->getValue());
        $this->assertSame('Pass No.', $sheet->getCell('G4')->getValue());
        $this->assertSame('Visit Type', $sheet->getCell('H4')->getValue());
        $this->assertSame('Duration', $sheet->getCell('N4')->getValue());
        $this->assertSame('Alert', $sheet->getCell('R4')->getValue());
        $this->assertNotSame('Identification Type', $sheet->getCell('F4')->getValue());
        $this->assertSame('Total Visitors:', $sheet->getCell('A6')->getValue());
        $this->assertSame(0, (int) $sheet->getCell('B6')->getValue());
        $this->assertNotNull($sheet->getAutoFilter()->getRange());
        $this->assertSame('A5', $sheet->getFreezePane());
    }

    #[Test]
    public function it_preserves_leading_zeros_for_control_contact_and_pass_numbers(): void
    {
        $service = new DailyVisitorReportService;
        $date = Carbon::parse('2026-08-01', 'Asia/Manila')->startOfDay();

        $rows = new Collection([
            [
                'visit_id' => '42',
                'control_number' => '001234',
                'visitor_full_name' => 'Juan Dela Cruz',
                'birth_date' => Carbon::parse('1990-01-15', 'Asia/Manila'),
                'address' => 'Sample Address',
                'contact_number' => '09171234567',
                'pass_number' => '00098765',
                'visit_type' => 'Visitor',
                'purpose_of_visit' => 'Meeting',
                'office_or_person' => 'Registrar',
                'date_of_visit' => $date->copy(),
                'time_in' => $date->copy()->setTime(9, 30, 0),
                'time_out' => $date->copy()->setTime(11, 0, 0),
                'duration' => '1 hr 30 mins',
                'visit_status' => 'Completed',
                'entry_method' => 'Guard Registration',
                'processed_by' => 'Guard One',
                'alert' => 'None',
                'remarks' => '',
            ],
        ]);

        $spreadsheet = $service->buildSpreadsheet($date, $rows);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('42', $sheet->getCell('A5')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B5')->getDataType());
        $this->assertSame('001234', $sheet->getCell('B5')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('F5')->getDataType());
        $this->assertSame('09171234567', $sheet->getCell('F5')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('G5')->getDataType());
        $this->assertSame('00098765', $sheet->getCell('G5')->getValue());
        $this->assertSame('Visitor', $sheet->getCell('H5')->getValue());
        $this->assertSame('1 hr 30 mins', $sheet->getCell('N5')->getValue());
        $this->assertSame('None', $sheet->getCell('R5')->getValue());
        $this->assertSame(1, (int) $sheet->getCell('B7')->getValue());
    }

    #[Test]
    public function it_rejects_invalid_and_future_dates(): void
    {
        $service = new DailyVisitorReportService;

        $this->expectException(RuntimeException::class);
        $service->normalizeReportDate('08-01-2026');
    }

    #[Test]
    public function it_rejects_future_dates(): void
    {
        $service = new DailyVisitorReportService;
        $future = Carbon::now('Asia/Manila')->addDay()->toDateString();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Future dates are not allowed');
        $service->normalizeReportDate($future);
    }

    #[Test]
    public function it_builds_expected_filename_and_storage_path(): void
    {
        $service = new DailyVisitorReportService;
        $fileName = $service->buildFileName('2026-08-01');

        $this->assertSame('NU-Secure_Visitor_Report_2026-08-01.xlsx', $fileName);
        $this->assertSame(
            'reports/daily/2026/08/NU-Secure_Visitor_Report_2026-08-01.xlsx',
            $service->buildRelativePath('2026-08-01', $fileName)
        );
    }

    #[Test]
    public function generated_workbook_can_be_saved_and_reopened(): void
    {
        $service = new DailyVisitorReportService;
        $date = Carbon::parse('2026-08-01', 'Asia/Manila')->startOfDay();
        $spreadsheet = $service->buildSpreadsheet($date, collect([]));

        $temp = tempnam(sys_get_temp_dir(), 'daily_report_').'.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($temp);

        $reloaded = IOFactory::load($temp);
        $this->assertSame('NU-Secure Daily Visitor Report', $reloaded->getActiveSheet()->getCell('A1')->getValue());

        @unlink($temp);
    }
}
