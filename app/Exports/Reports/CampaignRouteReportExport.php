<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CampaignRouteReportExport implements FromView, WithTitle, WithEvents
{
    public function __construct(private readonly array $data)
    {}

    public function view(): View
    {
        return view('reports.campaign-route', [
            'previousPeriod' => $this->data['previous_period'],
            'currentPeriod' => $this->data['current_period'],
            'governorates' => $this->data['governorates'],
            'items' => $this->data['items'],
            'summary' => $this->data['summary'],
        ]);
    }

    public function title(): string
    {
        return 'Campaign Route';
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
                    'A' => 9,
                    'B' => 18,
                    'C' => 42,
                    'D' => 44,
                    'E' => 44,
                    'F' => 18,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A8:F{$lastRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}
