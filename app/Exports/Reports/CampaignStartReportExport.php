<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

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
            'governorates' => $this->visibleGovernorates(),
            'items' => $this->data['items'],
            'summary' => $this->data['summary'],
        ]);
    }

    public function title(): string
    {
        return app()->getLocale() === 'ar' ? 'بداية الحملة' : 'Campaign Start';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $columnCount = count($this->visibleGovernorates()) * 2 + 4;
                $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
                $dataRows = max(1, count($this->data['items']));
                $lastRow = 9 + $dataRows + 5;

                $sheet->setRightToLeft(app()->getLocale() === 'ar');
                $sheet->setShowGridLines(false);
                $sheet->freezePane('C10');
                $sheet->getSheetView()->setZoomScale(90);
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Cairo');

                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(18);

                for ($column = 3; $column <= $columnCount; $column++) {
                    $sheet->getColumnDimension(
                        Coordinate::stringFromColumnIndex($column)
                    )->setWidth(13);
                }

                $rowHeights = [
                    1 => 43,
                    2 => 31,
                    3 => 12,
                    4 => 32,
                    5 => 30,
                    6 => 49,
                    7 => 12,
                    8 => 34,
                    9 => 29,
                ];

                foreach ($rowHeights as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }

                for ($row = 10; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(30);
                }

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
                $sheet->getPageSetup()->setPrintArea("A1:{$lastColumn}{$lastRow}");
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8, 9);
            },
        ];
    }

    private function visibleGovernorates(): array
    {
        $summary = $this->data['summary'];

        return array_values(array_filter(
            $this->data['governorates'],
            static function (array $governorate) use ($summary): bool {
                foreach (['booked', 'inventory'] as $key) {
                    foreach ($summary[$key]['governorates'] as $counts) {
                        if (
                            (string) $counts['governorate_id'] === (string) $governorate['id'] &&
                            ($counts['boards_count'] > 0 || $counts['networks_count'] > 0)
                        ) {
                            return true;
                        }
                    }
                }

                return false;
            }
        ));
    }
}
