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
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class CampaignSalesReportExport implements FromView, WithTitle, WithEvents
{
    public function __construct(private readonly array $data)
    {
    }

    public function view(): View
    {
        return view('reports.campaign-sales', [
            'period' => $this->data['period'],
            'items' => $this->data['items'],
            'summary' => $this->data['summary'],
        ]);
    }

    public function title(): string
    {
        return 'Campaign Sales';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $isArabic = app()->getLocale() === 'ar';
                $textAlignment = $isArabic
                    ? Alignment::HORIZONTAL_RIGHT
                    : Alignment::HORIZONTAL_LEFT;

                $sheet->setRightToLeft($isArabic);
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(85);
                $sheet->freezePane('A11');

                // Twelve underlying columns allow four equal KPI blocks and
                // five visible table columns (with merged company cells).
                $widths = [
                    'A' => 13, 'B' => 13, 'C' => 13, 'D' => 13,
                    'E' => 18,
                    'F' => 6.5, 'G' => 13,
                    'H' => 13, 'I' => 13, 'J' => 13,
                    'K' => 10, 'L' => 10,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                $itemRows = max(count($this->data['items']), 1);
                $totalRow = 11 + $itemRows;
                $detailHeaderRow = $totalRow + 2;
                $detailFirstRow = $detailHeaderRow + 1;
                $lastRow = $detailFirstRow + 4;

                $sheet->getStyle("A1:L{$lastRow}")
                    ->getFont()->setName('Arial');
                $sheet->getStyle("A1:L{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $fixedRowHeights = [
                    1 => 12,
                    2 => 48,
                    3 => 12,
                    4 => 44,
                    5 => 16,
                    6 => 36,
                    7 => 60,
                    8 => 16,
                    9 => 18,  // Spacer in place of the removed subtitle.
                    10 => 48,
                    $totalRow => 42,
                    $totalRow + 1 => 38, // Breathing room before the two summaries.
                    $detailHeaderRow => 46,
                ];

                foreach ($fixedRowHeights as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }

                for ($row = 11; $row < $totalRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(44);
                }

                for ($row = $detailFirstRow; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(38);
                }

                // Explicit fills for merged Excel cells (HTML styles alone
                // may not cover their full merged ranges in all readers).
                foreach ([
                    4 => 'FAF5FE',
                    6 => 'FAF6FD',
                    7 => 'FFFFFF',
                    9 => 'FFFFFF',
                    10 => '2F1660',
                    $totalRow => '2F1660',
                    $detailHeaderRow => '2F1660',
                ] as $row => $color) {
                    $sheet->getStyle("A{$row}:L{$row}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }

                $sheet->getStyle('K10:L10')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('49267B');
                $sheet->getStyle("K{$totalRow}:L{$totalRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('49267B');

                foreach ([
                    ['A', 'C'], ['D', 'F'], ['G', 'I'], ['J', 'L'],
                ] as [$start, $end]) {
                    foreach ([6, 7] as $row) {
                        $sheet->getStyle("{$start}{$row}:{$end}{$row}")
                            ->getBorders()->getOutline()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->getColor()->setRGB('E6E0E9');
                    }
                }

                foreach ([
                    ['A', 'D'], ['E', 'E'], ['F', 'G'],
                    ['H', 'J'], ['K', 'L'],
                ] as [$start, $end]) {
                    $sheet->getStyle("{$start}10:{$end}10")
                        ->getBorders()->getOutline()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('49347A');
                }

                $sheet->getStyle('A4:F4')->getAlignment()
                    ->setHorizontal($textAlignment);
                $sheet->getStyle('G4:L4')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$detailHeaderRow}:E{$detailHeaderRow}")
                    ->getAlignment()->setHorizontal($textAlignment);
                $sheet->getStyle("G{$detailHeaderRow}:L{$detailHeaderRow}")
                    ->getAlignment()->setHorizontal($textAlignment);

                // Only visual grouping, never summing or changing sales data.
                $groups = collect($this->data['items'])
                    ->groupBy(fn ($item) => $item['customer']['id']);

                $row = 11;
                $groupIndex = 0;
                foreach ($groups as $customerRows) {
                    $endRow = $row + count($customerRows) - 1;
                    $groupColor = $groupIndex % 2 === 0 ? 'FFFFFF' : 'FCFAFF';

                    $sheet->getStyle("A{$row}:D{$endRow}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($groupColor);
                    $sheet->getStyle("A{$row}:D{$endRow}")
                        ->getBorders()->getOutline()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('E8E1EB');
                    $sheet->getStyle("A{$row}:D{$endRow}")
                        ->getAlignment()->setHorizontal($textAlignment);

                    foreach ($customerRows as $item) {
                        $statusColor = $item['booking_status'] === 'confirmed'
                            ? 'F0E4FB'
                            : 'F5F2F7';

                        foreach ([
                            ['E', 'E', $statusColor],
                            ['F', 'G', $groupColor],
                            ['H', 'J', $groupColor],
                            ['K', 'L', 'F8F1FD'],
                        ] as [$first, $last, $background]) {
                            $range = "{$first}{$row}:{$last}{$row}";
                            $sheet->getStyle($range)
                                ->getFill()->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB($background);
                            $sheet->getStyle($range)
                                ->getBorders()->getOutline()
                                ->setBorderStyle(Border::BORDER_THIN)
                                ->getColor()->setRGB('E8E1EB');
                            $sheet->getStyle($range)->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        $row++;
                    }

                    $groupIndex++;
                }

                // Geographic summaries are equal-height, independent tables.
                for ($index = 0; $index < 5; $index++) {
                    $row = $detailFirstRow + $index;
                    $background = $index === 4
                        ? '2F1660'
                        : ($index % 2 === 0 ? 'FFFFFF' : 'FCFAFF');

                    foreach ([
                        ['A', 'D'], ['E', 'E'], ['G', 'J'], ['K', 'L'],
                    ] as [$first, $last]) {
                        $range = "{$first}{$row}:{$last}{$row}";
                        $sheet->getStyle($range)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($background);
                        $sheet->getStyle($range)->getBorders()->getOutline()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->getColor()->setRGB('E8E1EB');
                    }
                    $sheet->getStyle("A{$row}:D{$row}")->getAlignment()
                        ->setHorizontal($textAlignment);
                    $sheet->getStyle("G{$row}:J{$row}")->getAlignment()
                        ->setHorizontal($textAlignment);
                }

                // Preserve a visible white gutter between the two lower tables.
                // AfterSheet styles otherwise paint the entire header row purple.
                $gutter = $sheet->getStyle("F{$detailHeaderRow}:F{$lastRow}");
                $gutter->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFFFF');
                $gutter->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_NONE);

                // Print-friendly sheet without introducing separate worksheets.
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A3)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageMargins()
                    ->setLeft(0.25)
                    ->setRight(0.25)
                    ->setTop(0.4)
                    ->setBottom(0.4);
                $sheet->getPageSetup()->setPrintArea("A2:L{$lastRow}");
            },
        ];
    }
}
