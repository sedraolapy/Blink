<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CampaignStartReportExport implements
    FromView,
    WithTitle,
    WithEvents
{
    public function __construct(
        private readonly array $data
    ) {}

    public function view(): View
    {
        return view('reports.campaign-start', [
            'period' => $this->data['period'],
            'governorates' => $this->data['governorates'],
            'items' => $this->data['items'],
            'summary' => $this->data['summary'],
        ]);
    }

    public function title(): string
    {
        return 'Campaign Start';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(app()->getLocale() === 'ar');
                $sheet->freezePane('C10');
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(90);
            },
        ];
    }
}
