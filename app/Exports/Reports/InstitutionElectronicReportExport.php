<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class InstitutionElectronicReportExport implements FromView, WithTitle, WithEvents
{
    private const TABLE_HEADER_ROW = 7;

    public function __construct(
        private readonly array $report,
        private readonly string $from,
        private readonly string $to
    ) {}

    public function view(): View
    {
        return view('reports.institution-electronic', [
            'month' => $this->report['month'],
            'sections' => $this->report['sections'],
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    public function title(): string
    {
        return app()->getLocale() === 'ar'
            ? 'مؤسسة الإلكتروني'
            : 'Electronic Institution';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $isArabic = app()->getLocale() === 'ar';
                $lastRow = $sheet->getHighestRow();

                $sheet->setRightToLeft($isArabic);
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(85);

                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri');

                foreach ([
                    'A' => 9,
                    'B' => 68,
                    'C' => 25,
                    'D' => 25,
                    'E' => 14,
                    'F' => 20,
                    'G' => 20,
                ] as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                foreach ([
                    1 => 30,
                    2 => 26,
                    3 => 26,
                    4 => 10,
                    5 => 40,
                    6 => 12,
                    7 => 30,
                ] as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }

                for ($row = self::TABLE_HEADER_ROW + 1; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(30);
                }

                $sheet->getStyle("A1:G{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

foreach ([1, 2, 3, 5] as $row) {
    $sheet->getStyle("A{$row}:E{$row}")
        ->getAlignment()
        ->setHorizontal($isArabic
            ? Alignment::HORIZONTAL_RIGHT
            : Alignment::HORIZONTAL_LEFT)
        ->setWrapText(true);

    $sheet->getStyle("A{$row}:E{$row}")
        ->getFont()
        ->setBold(true);
}

                $tableRange = 'A' . self::TABLE_HEADER_ROW . ":G{$lastRow}";

                $sheet->getStyle($tableRange)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle($tableRange)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($tableRange)
                    ->getBorders()
                    ->getAllBorders()
                    ->getColor()
                    ->setARGB('FF4A4A4A');

                $sheet->getStyle('A7:G7')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEFEFEF');

                $sheet->getStyle('A7:G7')->getFont()->setBold(true);

                $row = self::TABLE_HEADER_ROW + 1;

                foreach ($this->report['sections'] as $section) {
                    foreach ($section['items'] as $item) {
                        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("E{$row}")->getFont()->setBold(true);
                        $row++;
                    }

                    $summaryRange = "A{$row}:G{$row}";
                    $sheet->getStyle($summaryRange)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFBFBFBF');
                    $sheet->getStyle($summaryRange)->getFont()->setBold(true);
                    $row++;
                }

                $logoPath = public_path('images/blink-logo.png');

                if (is_file($logoPath)) {
                    $logo = new Drawing();
                    $logo->setName('Blink');
                    $logo->setDescription('Blink logo');
                    $logo->setPath($logoPath);
                    $logo->setWidth(240);
                    $logo->setCoordinates('F1');
                    $logo->setOffsetX(5);
                    $logo->setOffsetY(42);
                    $logo->setWorksheet($sheet);
                }

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A3)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setPrintArea("A1:G{$lastRow}");

                $sheet->getPageSetup()
                    ->setRowsToRepeatAtTopByStartAndEnd(7, 7);

                $sheet->getPageMargins()
                    ->setLeft(0.3)
                    ->setRight(0.3)
                    ->setTop(0.4)
                    ->setBottom(0.4);
            },
        ];
    }
}
