<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CampaignSalesReportExport implements FromView, WithTitle, WithEvents
{
    public function __construct(private readonly array $data)
    {}

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
            AfterSheet::class => function (AfterSheet $event): void
            {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(app()->getLocale() === 'ar');
                $sheet->freezePane('A9');
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(90);
                $widths = [
                    'A' => 38,
                    'B' => 19,
                    'C' => 34,
                    'D' => 22,
                    'E' => 22,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A8:E{$lastRow}")
                    ->getAlignment()
                    ->setWrapText(true);
            },
        ];
    }
}
