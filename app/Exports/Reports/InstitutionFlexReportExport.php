<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class InstitutionFlexReportExport implements FromView, WithTitle, WithEvents
{
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

                $sheet->setRightToLeft($isArabic);
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(90);

                $widths = [
                    'A' => 38,
                    'B' => 22,
                    'C' => 20,
                    'D' => 22,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                $lastRow = max(1, $sheet->getHighestRow());

                $sheet->getStyle("A1:D{$lastRow}")
                    ->getFont()
                    ->setName('Arial');

                $sheet->getStyle("A1:D{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageMargins()
                    ->setLeft(0.3)
                    ->setRight(0.3)
                    ->setTop(0.4)
                    ->setBottom(0.4);

                $sheet->getPageSetup()
                    ->setPrintArea("A1:D{$lastRow}");
            },
        ];
    }
}
