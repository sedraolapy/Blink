<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class InstitutionFlexReportExport implements FromView, WithTitle, WithEvents
{
    private const TABLE_HEADER_ROW = 8;

    public function __construct(
        private readonly array $report,
        private readonly string $from,
        private readonly string $to,
        private readonly ?string $bookedLabel = null,
        private readonly ?string $remainingLabel = null,
    ) {}

    public function view(): View
    {
        return view('reports.institution-flex', [
            'period' => $this->report['period'],
            'sections' => $this->report['sections'],
            'from' => $this->from,
            'to' => $this->to,
            'bookedLabel' => $this->bookedLabel,
            'remainingLabel' => $this->remainingLabel,
        ]);
    }

    public function title(): string
    {
        return app()->getLocale() === 'ar'
            ? 'مؤسسات الفليكس'
            : 'Flex Institutions';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $isArabic = app()->getLocale() === 'ar';
                $isDamascus = request()->query('region') === 'damascus';
                $lastRow = $sheet->getHighestRow();

                $sheet->setRightToLeft($isArabic);
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(85);

                $sheet->getParent()->getDefaultStyle()->getFont()
                    ->setName('Calibri')
                    ->setSize(16);

                $sheet->getStyle("A1:E{$lastRow}")->getFont()
                    ->setName('Calibri')
                    ->setSize(16);

                foreach ([
                    'A' => 15,
                    'B' => 30,
                    'C' => 25,
                    'D' => 26,
                    'E' => 29,
                ] as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                foreach ([
                    1 => 41,
                    2 => 36,
                    3 => 36,
                    4 => 26,
                    5 => 42,
                    6 => 40,
                    7 => 16,
                    8 => 42,
                ] as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }

                $sheet->getStyle("A1:E{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $paragraphAlign = $isArabic
                    ? Alignment::HORIZONTAL_RIGHT
                    : Alignment::HORIZONTAL_LEFT;

                foreach ([1, 2, 3, 6] as $row) {
                    $sheet->getStyle("A{$row}:E{$row}")
                        ->getAlignment()
                        ->setHorizontal($paragraphAlign)
                        ->setWrapText(true);
                    $sheet->getStyle("A{$row}:E{$row}")
                        ->getFont()
                        ->setBold(true)
                        ->setSize(16);
                }

                $sheet->getStyle('A8:E8')->getFont()
                    ->setBold(true)
                    ->setSize(14);
                $sheet->getStyle('A8:E8')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $row = self::TABLE_HEADER_ROW + 1;

                foreach ($this->report['sections'] as $index => $section) {
                    if ($index > 0) {
                        $sheet->getRowDimension($row)->setRowHeight(20);
                        $row++;
                    }

                    $rowCount = 0;
                    foreach ($section['customers'] as $entry) {
                        $rowCount += count($entry['items']);
                    }

                    $startRow = $row;
                    for ($i = 0; $i < $rowCount; $i++) {
                        $sheet->getRowDimension($row)->setRowHeight(38);
                        $sheet->getStyle("A{$row}:E{$row}")
                            ->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER)
                            ->setWrapText(true);
                        $sheet->getStyle("B{$row}")->getAlignment()
                            ->setHorizontal($paragraphAlign);
                        $sheet->getStyle("B{$row}")->getFont()
                            ->setBold(true);
                        $sheet->getStyle("C{$row}:E{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $row++;
                    }


if ($rowCount > 0) {
    $cell = "A{$startRow}";

    $label = trim((string) $sheet->getCell($cell)->getValue());

    $availableHeight = $rowCount * 38;
    $requiredHeight = mb_strlen($label, 'UTF-8') * 13 + 16;

    $canRotate =
        $isDamascus &&
        $rowCount >= 3 &&
        $availableHeight >= $requiredHeight;

    $alignment = $sheet->getStyle($cell)->getAlignment();

    $alignment
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(!$canRotate)
        ->setTextRotation($canRotate ? -90 : 0);

    $sheet->getStyle($cell)
        ->getFont()
        ->setBold(true);

    if (!$canRotate && $rowCount === 1) {
        $sheet->getRowDimension($startRow)
            ->setRowHeight(58);
    }
}

                    foreach ([$row, $row + 1] as $summaryRow) {
                        $sheet->getRowDimension($summaryRow)->setRowHeight(39);
                        $sheet->getStyle("A{$summaryRow}:E{$summaryRow}")
                            ->getFont()
                            ->setBold(true)
                            ->setSize(16);
                        $sheet->getStyle("A{$summaryRow}:B{$summaryRow}")
                            ->getAlignment()
                            ->setHorizontal($paragraphAlign);
                        $sheet->getStyle("C{$summaryRow}:E{$summaryRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    $row += 2;
                }

                $logoPath = public_path('images/blink-logo.png');

                if (is_file($logoPath)) {
                    $logo = new Drawing();
                    $logo->setName('Blink');
                    $logo->setDescription('Blink logo');
                    $logo->setPath($logoPath);
                    $logo->setWidth(230);
                    $logo->setCoordinates('E2');
                    $logo->setOffsetX(5);
                    $logo->setOffsetY(12);
                    $logo->setWorksheet($sheet);
                }

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A3)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setPrintArea("A1:E{$lastRow}")
                    ->setRowsToRepeatAtTopByStartAndEnd(8, 8);

                $sheet->getPageMargins()
                    ->setLeft(0.3)
                    ->setRight(0.3)
                    ->setTop(0.4)
                    ->setBottom(0.4);
            },
        ];
    }
}
