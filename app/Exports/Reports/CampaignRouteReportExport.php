<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class CampaignRouteReportExport implements FromView, WithTitle, WithEvents
{
    /** Data prepared once for both Blade rendering and AfterSheet styling. */
    private array $rows;

    public function __construct(private readonly array $data)
    {
        $this->rows = collect($data['items'])
            ->map(fn ($item) => $this->normalizeRow($item))
            ->values()
            ->all();

    }

    public function view(): View
    {
        return view('reports.campaign-route', [
            'previousPeriod' => $this->data['previous_period'],
            'currentPeriod' => $this->data['current_period'],
            'items' => $this->rows,
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
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $isArabic = app()->getLocale() === 'ar';
                $textAlignment = $isArabic
                    ? Alignment::HORIZONTAL_RIGHT
                    : Alignment::HORIZONTAL_LEFT;

                $sheet->setRightToLeft($isArabic);
                $sheet->setShowGridLines(false);
                $sheet->getSheetView()->setZoomScale(85);

                // Header and title occupy rows 1–10. Avoid freezing across
                // merged columns, which would split the report's panels.
                $sheet->freezePane('A11');

                // Twelve underlying columns create five visual table columns.
                $widths = [
                    'A' => 12, 'B' => 12,
                    'C' => 14, 'D' => 14, 'E' => 14,
                    'F' => 14, 'G' => 14, 'H' => 14,
                    'I' => 14, 'J' => 14, 'K' => 14,
                    'L' => 17,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(false)
                        ->setWidth($width);
                }

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A1:L{$lastRow}")
                    ->getFont()->setName('Arial');

                $sheet->getStyle("A1:L{$lastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                foreach ([
                    1 => 12,
                    2 => 46,
                    3 => 12,
                    4 => 52,
                    5 => 6,
                    6 => 6,
                    7 => 35,
                    8 => 66,
                    9 => 14,
                    10 => 46,
                ] as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }

                $sheet->getStyle('A4:C4')->getAlignment()
                    ->setHorizontal($textAlignment);
                $sheet->getStyle('G4:I4')->getAlignment()
                    ->setHorizontal($textAlignment);

                // Fill merged sections across all their underlying columns.
                foreach ([
                    4 => 'FAF6FD',
                    7 => 'FAF6FD',
                    8 => 'FFFFFF',
                    10 => '2F1660',
                ] as $row => $background) {
                    $sheet->getStyle("A{$row}:L{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($background);
                }

                foreach ([
                    ['A', 'C'], ['D', 'F'], ['G', 'I'], ['J', 'L'],
                ] as [$first, $last]) {
                    foreach ([4, 7, 8] as $row) {
                        $sheet->getStyle("{$first}{$row}:{$last}{$row}")
                            ->getBorders()->getOutline()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->getColor()->setRGB('E6E0E9');
                    }
                }

                $labels = $isArabic
                    ? ['current' => 'الفترة الحالية', 'previous' => 'الفترة السابقة', 'period' => 'الفترة']
                    : ['current' => 'Current Period', 'previous' => 'Previous Period', 'period' => 'Period'];

                foreach ([
                    ['A4', 'current', $this->data['current_period']],
                    ['G4', 'previous', $this->data['previous_period']],
                ] as [$cell, $label, $period]) {
                    $sheet->setCellValue(
                        $cell,
                        $this->periodText(
                            $labels[$label],
                            "{$labels['period']} {$period['number']} · {$period['year']}"
                        )
                    );
                }

                // Table data stays in Laravel's order and uses Laravel's
                // required_action value; no classifications are recalculated.
                $cellGroups = [
                    ['A', 'B'],
                    ['C', 'E'],
                    ['F', 'H'],
                    ['I', 'K'],
                    ['L', 'L'],
                ];

                foreach ($this->rows as $index => $item) {
                    $row = 11 + $index;
                    $background = $index % 2 === 0 ? 'FFFFFF' : 'FCFAFF';

                    $sheet->getRowDimension($row)->setRowHeight(54);
                    $sheet->getStyle("A{$row}:L{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($background);

                    foreach ($cellGroups as [$first, $last]) {
                        $sheet->getStyle("{$first}{$row}:{$last}{$row}")
                            ->getBorders()->getOutline()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->getColor()->setRGB('E8E1EB');
                    }

                    $sheet->getStyle("A{$row}:K{$row}")
                        ->getAlignment()->setHorizontal($textAlignment);
                    $sheet->getStyle("L{$row}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->setCellValue(
                        "C{$row}",
                        $this->twoLineText(
                            $item['board']['name'],
                            $item['board']['code']
                        )
                    );

                    $noDesign = $isArabic ? 'لا يوجد تصميم' : 'No design';
                    foreach (['previous_booking' => 'F', 'current_booking' => 'I'] as $key => $column) {
                        $booking = $item[$key];

                        $sheet->setCellValue(
                            "{$column}{$row}",
                            $booking === null
                                ? '—'
                                : $this->twoLineText(
                                    $booking['customer']['name'],
                                    $booking['design_name'] ?? $noDesign
                                )
                        );
                    }

                    $action = $item['required_action'];

                    if ($action === 'installation' || $action === 'extension') {
                        $color = $action === 'installation' ? 'F0E4FB' : 'E9E2F3';

                        $sheet->getStyle("L{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($color);
                    } else {
                        // "no_order" is visually empty, including no dash.
                        $sheet->setCellValue("L{$row}", '');
                    }
                }

                if ($this->rows === []) {
                    $sheet->getRowDimension(11)->setRowHeight(55);
                }

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

    /**
     * The export service currently sends Eloquent models with previous_item and
     * current_item; the JSON preview sends prepared previous_booking/current_booking
     * arrays. Normalize either representation here, without recalculating actions.
     */
    private function normalizeRow(mixed $item): array
    {
        $board = data_get($item, 'board');
        $governorate = data_get($item, 'governorate');

        // Accept the existing Eloquent projection or the already normalized API shape.
        $isPrepared = \Illuminate\Support\Arr::has($item, 'previous_booking')
            && \Illuminate\Support\Arr::has($item, 'current_booking');

        if (! $isPrepared && (
            ! \Illuminate\Support\Arr::has($item, 'previous_item')
            || ! \Illuminate\Support\Arr::has($item, 'current_item')
        )) {
            throw new \UnexpectedValueException('Campaign Route export: booking fields are missing from a row.');
        }

        return [
            'governorate' => [
                'id' => data_get($governorate, 'id'),
                'name' => $this->localized($governorate, 'name'),
            ],
            'board' => [
                'id' => data_get($board, 'id'),
                'code' => $this->localized($board, 'code'),
                'name' => $this->localized($board, 'name')
                    ?? $this->localized($board, 'location_name'),
            ],
            'previous_booking' => $this->normalizeBooking(
                data_get($item, $isPrepared ? 'previous_booking' : 'previous_item')
            ),
            'current_booking' => $this->normalizeBooking(
                data_get($item, $isPrepared ? 'current_booking' : 'current_item')
            ),
            // Classification is passed through unchanged; Laravel fixes its rule.
            'required_action' => data_get($item, 'required_action'),
        ];
    }

    private function normalizeBooking(mixed $booking): ?array
    {
        if ($booking === null) {
            return null;
        }

        $customer = data_get($booking, 'customer')
            ?? data_get($booking, 'period.flexBooking.booking.customer');

        if ($customer === null) {
            throw new \UnexpectedValueException('Campaign Route export: a booked item lacks its customer relation.');
        }

        $designName = $this->localized($booking, 'design_name');
        $design = data_get($booking, 'design');

        if ($designName === null && $design !== null) {
            $designName = $this->localized($design, 'name')
                ?? $this->localized($design, 'design_name');

            if ($designName === null) {
                throw new \UnexpectedValueException('Campaign Route export: a present design has no supported display name.');
            }
        }

        return [
            'customer' => [
                'id' => data_get($customer, 'id'),
                'name' => $this->localized($customer, 'name'),
            ],
            'design_name' => $designName,
        ];
    }

    /** Retrieve localized model attributes without treating locale arrays as strings. */
    private function localized(mixed $source, string $field): ?string
    {
        $value = data_get($source, $field);
        if (is_array($value)) {
            $value = $value[app()->getLocale()] ?? null;
        }

        return is_scalar($value) ? (string) $value : null;
    }

    private function periodText(string $label, string $period): RichText
    {
        $text = new RichText();

        $firstLine = $text->createTextRun($label);
        $firstLine->getFont()
            ->setName('Arial')
            ->setSize(9)
            ->getColor()->setRGB('94849C');

        $secondLine = $text->createTextRun("\n" . $period);
        $secondLine->getFont()
            ->setName('Arial')
            ->setSize(12)
            ->setBold(true)
            ->getColor()->setRGB('2F1660');

        return $text;
    }

    private function twoLineText(?string $primary, ?string $secondary): RichText
    {
        $text = new RichText();
        $hasPrimary = $primary !== null && $primary !== '';

        if ($hasPrimary) {
            $firstLine = $text->createTextRun($primary);
            $firstLine->getFont()
                ->setName('Arial')
                ->setSize(10)
                ->setBold(true)
                ->getColor()->setRGB('3F3347');
        }

        if ($secondary !== null && $secondary !== '') {
            $secondLine = $text->createTextRun(($hasPrimary ? "\n" : '') . $secondary);
            $secondLine->getFont()
                ->setName('Arial')
                ->setSize(9)
                ->getColor()->setRGB('908496');
        }

        return $text;
    }
}
