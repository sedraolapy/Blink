<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class InstitutionOutdoorReportExport implements FromView, WithTitle, WithEvents
{
    public function __construct(
        private readonly array $report,
        private readonly string $outdoorType,
        private readonly string $from,
        private readonly string $to
    ) {}

    public function view(): View
    {
        return view('reports.institution-outdoor', [
            'report' => $this->report,
            'outdoorType' => $this->outdoorType,
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    public function title(): string
    {
        return app()->getLocale() === 'ar'
            ? 'مؤسسة الخارجي'
            : 'Outdoor Institution';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $isArabic = app()->getLocale() === 'ar';

                $sheet->setRightToLeft($isArabic);
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(85);

                $widths = $this->getColumnWidths();

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                $lastColumn = array_key_last($widths);
                $lastRow = max(1, $sheet->getHighestRow());

                $range = "A1:{$lastColumn}{$lastRow}";

                $sheet->getStyle($range)
                    ->getFont()
                    ->setName('Arial');

                $sheet->getStyle($range)
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A3)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageMargins()
                    ->setLeft(0.3)
                    ->setRight(0.3)
                    ->setTop(0.4)
                    ->setBottom(0.4);

                $sheet->getPageSetup()
                    ->setPrintArea($range);
            },
        ];
    }

    private function getColumnWidths(): array
    {
        return match ($this->outdoorType) {
            'mural', 'rooftop' => [
                'A' => 22, // City
                'B' => 50, // Location
                'C' => 34, // Customer
                'D' => 17, // Width
                'E' => 17, // Height
                'F' => 23, // Total area
            ],

            'tunnel' => [
                'A' => 55, // Location
                'B' => 36, // Customer
                'C' => 22, // Dimensions
                'D' => 23, // Total area
            ],

            'bridge' => [
                'A' => 55, // Location
                'B' => 36, // Customer
                'C' => 18, // Width
                'D' => 18, // Height
                'E' => 23, // Total area
            ],

            'unipole' => [
                'A' => 22, // City
                'B' => 32, // Connecting road
                'C' => 55, // Location
                'D' => 36, // Customer
                'E' => 22, // Dimensions
                'F' => 23, // Total area
            ],
        };
    }
}
