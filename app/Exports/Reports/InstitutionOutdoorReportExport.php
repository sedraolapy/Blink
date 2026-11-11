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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InstitutionOutdoorReportExport implements FromView, WithTitle, WithEvents
{
    private const STANDARD_HEADER_ROW = 8;
    private const TUNNEL_HEADER_ROW = 8;
    private const BRIDGE_HEADER_ROW = 8;
    private const UNIPOLE_HEADER_ROW = 8;
    public function __construct(
        private readonly array $report,
        private readonly string $outdoorType,
        private readonly string $from,
        private readonly string $to
    ) {}

    public function view(): View
    {
        $view = match ($this->outdoorType) {
            'mural' => 'reports.institution-outdoor.mural',
            'rooftop' => 'reports.institution-outdoor.rooftop',
            'tunnel' => 'reports.institution-outdoor.tunnel',
            'bridge' => 'reports.institution-outdoor.bridge',
            'unipole' => 'reports.institution-outdoor.unipole',
        };
        return view($view, [
            'report' => $this->report,
            'outdoorType' => $this->outdoorType,
            'from' => $this->from,
            'to' => $this->to,
            'bridgeSections' => $this->outdoorType === 'bridge'
                ? $this->getBridgeSections()
                : [],
            'unipoleRows' => $this->outdoorType === 'unipole'
                ? $this->getUnipoleRows()
                : [],
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

                $sheet->setRightToLeft(app()->getLocale() === 'ar');
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

                $sheet->getStyle($range)->getFont()->setName(
                    in_array($this->outdoorType, ['mural', 'rooftop', 'tunnel', 'bridge', 'unipole'], true)
                        ? 'Calibri'
                        : 'Arial'
                );
                $sheet->getStyle($range)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                if (in_array($this->outdoorType, ['mural', 'rooftop'], true)) {
                    $this->styleStandardSheet($sheet, $lastRow);
                    $this->addBlinkLogo($sheet);
                } elseif ($this->outdoorType === 'tunnel') {
                    $this->styleTunnelSheet($sheet, $lastRow);
                    $this->addBlinkLogo($sheet, 'F2');
                } elseif ($this->outdoorType === 'bridge') {
                    $this->styleBridgeSheet($sheet, $lastRow);
                    $this->addBlinkLogo($sheet, 'F2');
                } elseif ($this->outdoorType === 'unipole') {
                    $this->styleUnipoleSheet($sheet, $lastRow);
                    $this->addBlinkLogo($sheet, 'F2');
                }
                $this->configurePrint($sheet, $range);
            },
        ];
    }

    private function getColumnWidths(): array
    {
        return match ($this->outdoorType) {
            // The eight columns follow the approved React mural preview proportions.
            'mural', 'rooftop' => [
                'A' => 8,   // Sequence number
                'B' => 10,  // City
                'C' => 38,  // Location
                'D' => 19,  // Type
                'E' => 19,  // Advertising material
                'F' => 19,  // Width
                'G' => 19,  // Height
                'H' => 19,  // Total area
            ],

'tunnel' => [
                'A' => 8,   // Sequence
    'B' => 15,  // City
    'C' => 64,  // Tunnel location
    'D' => 17,  // Advertising material
    'E' => 13,  // Type
    'F' => 17,  // Dimensions
    'G' => 20,  // Total area
            ],
            'bridge' => [
                'A' => 8,   // Bridge number
                'B' => 11,  // City
                'C' => 60,  // Bridge location
                'D' => 29,  // Advertising material
                'E' => 16,  // Width
                'F' => 16,  // Height
                'G' => 16,  // Area
            ],
            'unipole' => [
                'A' => 8,   // Sequence number
                'B' => 13,  // City
                'C' => 23,  // Connecting road
                'D' => 67,  // Unipole location
                'E' => 21,  // Advertising material
                'F' => 18,  // Dimensions
                'G' => 20,  // Area
            ],
        };
    }

    private function styleStandardSheet(Worksheet $sheet, int $lastRow): void
    {
        $items = $this->report['items'];
        $itemCount = count($items);
        $summaryRow = self::STANDARD_HEADER_ROW + max(1, $itemCount) + 1;

        $sheet->getStyle("A1:H{$lastRow}")->getFont()
            ->setName('Calibri')
            ->getColor()->setARGB('FF161117');

        foreach ([
            1 => 36,
            2 => 34,
            3 => 34,
            4 => 20,
            5 => 33,
            6 => 42,
            7 => 15,
            self::STANDARD_HEADER_ROW => 39,
        ] as $row => $height) {
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        $paragraphAlignment = app()->getLocale() === 'ar'
            ? Alignment::HORIZONTAL_RIGHT
            : Alignment::HORIZONTAL_LEFT;

        foreach ([1, 2, 3] as $row) {
            $sheet->getStyle("A{$row}")->getFont()
                ->setSize(16)
                ->setBold(true);
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal($paragraphAlignment)
                ->setWrapText(true);
        }

        $sheet->getStyle('A6')->getFont()
            ->setSize(12)
            ->setBold(true);
        $sheet->getStyle('A6')->getAlignment()
            ->setHorizontal($paragraphAlignment)
            ->setWrapText(true);

        $tableRange = 'A' . self::STANDARD_HEADER_ROW . ":H{$lastRow}";
        $sheet->getStyle($tableRange)->getFont()->setSize(12);
        $sheet->getStyle($tableRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->getColor()->setARGB('FF4A4A4A');

        $sheet->getStyle('A8:H8')->getFont()
            ->setSize(10.5)
            ->setBold(true);
        $sheet->getStyle('A8:H8')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEFEFEF');

        $sheet->getStyle("F9:H{$lastRow}")->getNumberFormat()
        ->setFormatCode('General');

        $row = self::STANDARD_HEADER_ROW + 1;
        foreach ($items as $item) {
            $locationLength = mb_strlen((string) $item['location'], 'UTF-8');
            $materialLength = mb_strlen((string) $item['customer']['name'], 'UTF-8');
            $lineCount = max(
                1,
                (int) ceil($locationLength / 30),
                (int) ceil($materialLength / 18)
            );

            $sheet->getRowDimension($row)
                ->setRowHeight(max(39, 14 + (18 * $lineCount)));
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("B{$row}:D{$row}")->getFont()->setBold(true);
            $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $row++;
        }

        if ($itemCount === 0) {
            $sheet->getRowDimension($row)->setRowHeight(50);
        }

        $sheet->getRowDimension($summaryRow)->setRowHeight(39);
        $sheet->getStyle("A{$summaryRow}:H{$summaryRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');
        $sheet->getStyle("A{$summaryRow}:H{$summaryRow}")->getFont()
            ->setBold(true);
        $sheet->getStyle("H{$summaryRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$summaryRow}")->getNumberFormat()
            ->setFormatCode('#,##0.00');
    }

    private function styleTunnelSheet(
    Worksheet $sheet,
    int $lastRow
): void {
    $sections = $this->report['sections'] ?? [];

    $paragraphAlignment = app()->getLocale() === 'ar'
        ? Alignment::HORIZONTAL_RIGHT
        : Alignment::HORIZONTAL_LEFT;

    $sheet->getStyle("A1:G{$lastRow}")
        ->getFont()
        ->setName('Calibri')
        ->getColor()
        ->setARGB('FF161117');

    foreach ([
        1 => 36,
        2 => 34,
        3 => 34,
        4 => 20,
        5 => 33,
        6 => 42,
        7 => 15,
        self::TUNNEL_HEADER_ROW => 39,
    ] as $row => $height) {
        $sheet->getRowDimension($row)->setRowHeight($height);
    }

    foreach ([1, 2, 3] as $row) {
        $sheet->getStyle("A{$row}")
            ->getFont()
            ->setSize(16)
            ->setBold(true);

        $sheet->getStyle("A{$row}")
            ->getAlignment()
            ->setHorizontal($paragraphAlignment)
            ->setWrapText(true);
    }

    $sheet->getStyle('A6')
        ->getFont()
        ->setSize(12)
        ->setBold(true);

    $sheet->getStyle('A6')
        ->getAlignment()
        ->setHorizontal($paragraphAlignment)
        ->setWrapText(true);

    $tableRange =
        'A' . self::TUNNEL_HEADER_ROW . ":G{$lastRow}";

    $sheet->getStyle($tableRange)
        ->getFont()
        ->setSize(12);

    $sheet->getStyle($tableRange)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);

    $sheet->getStyle($tableRange)
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()
        ->setARGB('FF4A4A4A');

    $sheet->getStyle('A8:G8')
        ->getFont()
        ->setSize(10.5)
        ->setBold(true);

    $sheet->getStyle('A8:G8')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('FFEFEFEF');

    $row = self::TUNNEL_HEADER_ROW + 1;

    foreach ($sections as $section) {
        foreach ($section['items'] as $item) {
            $locationLength = mb_strlen(
                (string) $item['location'],
                'UTF-8'
            );

            $customerLength = mb_strlen(
                (string) $item['customer']['name'],
                'UTF-8'
            );

            $lines = max(
                1,
                (int) ceil($locationLength / 45),
                (int) ceil($customerLength / 16)
            );

            $sheet->getRowDimension($row)
                ->setRowHeight(max(39, 14 + (18 * $lines)));

            $sheet->getStyle("A{$row}:C{$row}")
                ->getFont()
                ->setBold(true);

            $sheet->getStyle("E{$row}:G{$row}")
                ->getFont()
                ->setBold(true);

$sheet->getStyle("G{$row}")
    ->getNumberFormat()
    ->setFormatCode('General');
                $row++;
        }

        $this->styleTunnelSummaryRow($sheet, $row);
        $row++;
    }

    if ($sections === []) {
        $sheet->getRowDimension($row)->setRowHeight(50);
        $row++;
    }

    $this->styleTunnelSummaryRow($sheet, $row);
}

    private function styleTunnelSummaryRow(
    Worksheet $sheet,
    int $row
): void {
    $sheet->getRowDimension($row)->setRowHeight(39);

    $sheet->getStyle("A{$row}:G{$row}")
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('FFBFBFBF');

    $sheet->getStyle("A{$row}:G{$row}")
        ->getFont()
        ->setBold(true);

    $sheet->getStyle("G{$row}")
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->getStyle("G{$row}")
        ->getNumberFormat()
        ->setFormatCode('#,##0.00');
}


    /**
     * Build the same consecutive bridge groups used by the approved frontend.
     * No area values are computed here; all amounts remain backend-provided.
     */
    private function getBridgeSections(): array
    {
        $result = [];

        foreach (($this->report['sections'] ?? []) as $section) {
            $items = $section['items'] ?? [];
            $groups = [];

            foreach ($items as $index => $item) {
                $bridgeId = $item['bridge_id'] ?? ($item['bridge']['id'] ?? null);
                $location = trim((string) ($item['location'] ?? ''));

                if ($bridgeId !== null) {
                    $key = 'id:' . (string) $bridgeId;
                } elseif (preg_match('/^(.+?)\s+[—–-]\s+.+$/u', $location, $matches) === 1) {
                    $key = 'location:' . trim($matches[1]);
                } else {
                    $key = 'row:' . $index;
                }

                $lastIndex = count($groups) - 1;

                if ($lastIndex >= 0 && $groups[$lastIndex]['key'] === $key) {
                    $groups[$lastIndex]['items'][] = $item;
                } else {
                    $groups[] = [
                        'key' => $key,
                        'items' => [$item],
                    ];
                }
            }

            $result[] = [
                'city' => $section['city'],
                'groups' => $groups,
                'item_count' => count($items),
                'summary' => $section['summary'],
            ];
        }

        return $result;
    }

    private function styleBridgeSheet(Worksheet $sheet, int $lastRow): void
    {
        $sections = $this->getBridgeSections();
        $isArabic = app()->getLocale() === 'ar';
        $paragraphAlignment = $isArabic
            ? Alignment::HORIZONTAL_RIGHT
            : Alignment::HORIZONTAL_LEFT;

        $sheet->getStyle("A1:G{$lastRow}")
            ->getFont()
            ->setName('Calibri')
            ->getColor()
            ->setARGB('FF161117');

        foreach ([
            1 => 36,
            2 => 34,
            3 => 34,
            4 => 20,
            5 => 33,
            6 => 42,
            7 => 15,
            self::BRIDGE_HEADER_ROW => 39,
        ] as $row => $height) {
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        foreach ([1, 2, 3] as $row) {
            $sheet->getStyle("A{$row}")->getFont()
                ->setSize(16)
                ->setBold(true);
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal($paragraphAlignment)
                ->setWrapText(true);
        }

        $sheet->getStyle('A6')->getFont()
            ->setSize(12)
            ->setBold(true);
        $sheet->getStyle('A6')->getAlignment()
            ->setHorizontal($paragraphAlignment)
            ->setWrapText(true);

        $tableRange = 'A' . self::BRIDGE_HEADER_ROW . ":G{$lastRow}";

        $sheet->getStyle($tableRange)->getFont()->setSize(12);
        $sheet->getStyle($tableRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB('FF4A4A4A');

        $sheet->getStyle('A8:G8')->getFont()
            ->setSize(10.5)
            ->setBold(true);
        $sheet->getStyle('A8:G8')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');

        $row = self::BRIDGE_HEADER_ROW + 1;

        foreach ($sections as $section) {
            $sectionStartRow = $row;
            $cityHeight = 0;

            foreach ($section['groups'] as $group) {
                $groupStartRow = $row;

                foreach ($group['items'] as $item) {
                    $locationLength = mb_strlen((string) ($item['location'] ?? ''), 'UTF-8');
                    $customerLength = mb_strlen((string) ($item['customer']['name'] ?? ''), 'UTF-8');
                    $lines = max(
                        1,
                        (int) ceil($locationLength / 48),
                        (int) ceil($customerLength / 23)
                    );
                    $height = max(39, 14 + 18 * $lines);
                    $sheet->getRowDimension($row)->setRowHeight($height);
                    $cityHeight += $height;

                    $sheet->getStyle("C{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("G{$row}")->getFont()->setBold(true);
$sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()
    ->setFormatCode('General');
                        $row++;
                }

                $sheet->getStyle("A{$groupStartRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$groupStartRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }

            // City merge is created by rowspan in Blade and ends before this summary.
            if ($section['item_count'] > 0) {
                $cityName = (string) ($section['city']['name'] ?? '');
                $requiredHeight = mb_strlen($cityName, 'UTF-8') * 9 + 20;
                $canRotate = $section['item_count'] > 1 && $cityHeight >= $requiredHeight;

                $cityStyle = $sheet->getStyle("B{$sectionStartRow}");
                $cityStyle->getFont()->setBold(true)->setSize(11);
                $cityStyle->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(!$canRotate)
                    ->setTextRotation($canRotate ? 90 : 0);
            }

            $this->styleBridgeSummaryRow($sheet, $row);
            $row++;
        }

        if ($sections === []) {
            $sheet->getRowDimension($row)->setRowHeight(50);
        }
    }

    private function styleBridgeSummaryRow(Worksheet $sheet, int $row): void
    {
        $sheet->getRowDimension($row)->setRowHeight(39);
        $sheet->getStyle("A{$row}:G{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:G{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle("G{$row}")->getNumberFormat()
    ->setFormatCode('General');
        }

    /**
     * Preserve API order and merge only consecutive rows in the same city / road.
     * Mirrors the grouping used in InstitutionOutdoorUnipoleTable.jsx.
     */
    private function getUnipoleRows(): array
    {
        $items = $this->report['items'] ?? [];
        $count = count($items);
        $rows = [];

        foreach ($items as $item) {
            $rows[] = [
                'item' => $item,
                'city_span' => 0,
                'road_span' => 0,
            ];
        }

        $start = 0;

        while ($start < $count) {
            $cityId = $items[$start]['city']['id'];
            $cityEnd = $start + 1;

            while (
                $cityEnd < $count &&
                $items[$cityEnd]['city']['id'] === $cityId
            ) {
                $cityEnd++;
            }

            $rows[$start]['city_span'] = $cityEnd - $start;

            $roadStart = $start;

            while ($roadStart < $cityEnd) {
                $road = $items[$roadStart]['connecting_road'];
                $roadEnd = $roadStart + 1;

                while (
                    $roadEnd < $cityEnd &&
                    $items[$roadEnd]['connecting_road'] === $road
                ) {
                    $roadEnd++;
                }

                $rows[$roadStart]['road_span'] = $roadEnd - $roadStart;
                $roadStart = $roadEnd;
            }

            $start = $cityEnd;
        }

        return $rows;
    }

    private function styleUnipoleSheet(Worksheet $sheet, int $lastRow): void
    {
        $rows = $this->getUnipoleRows();
        $isArabic = app()->getLocale() === 'ar';
        $paragraphAlignment = $isArabic
            ? Alignment::HORIZONTAL_RIGHT
            : Alignment::HORIZONTAL_LEFT;

        $sheet->getStyle("A1:G{$lastRow}")->getFont()
            ->setName('Calibri')
            ->getColor()->setARGB('FF161117');

        foreach ([
            1 => 36,
            2 => 34,
            3 => 34,
            4 => 20,
            5 => 33,
            6 => 42,
            7 => 15,
            self::UNIPOLE_HEADER_ROW => 39,
        ] as $row => $height) {
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        foreach ([1, 2, 3] as $row) {
            $sheet->getStyle("A{$row}")->getFont()
                ->setSize(16)
                ->setBold(true);
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal($paragraphAlignment)
                ->setWrapText(true);
        }

        $sheet->getStyle('A6')->getFont()
            ->setSize(12)
            ->setBold(true);
        $sheet->getStyle('A6')->getAlignment()
            ->setHorizontal($paragraphAlignment)
            ->setWrapText(true);

        $tableRange = 'A' . self::UNIPOLE_HEADER_ROW . ":G{$lastRow}";

        $sheet->getStyle($tableRange)->getFont()->setSize(12);
        $sheet->getStyle($tableRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB('FF4A4A4A');

        $headerRange = 'A' . self::UNIPOLE_HEADER_ROW
            . ':G' . self::UNIPOLE_HEADER_ROW;

        $sheet->getStyle($headerRange)->getFont()
            ->setSize(10.5)
            ->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDEDEDE');

        $row = self::UNIPOLE_HEADER_ROW + 1;

        foreach ($rows as $entry) {
            $item = $entry['item'];

            $locationLength = mb_strlen((string) $item['location'], 'UTF-8');
            $customerLength = mb_strlen((string) $item['customer']['name'], 'UTF-8');
            $lines = max(
                1,
                (int) ceil($locationLength / 52),
                (int) ceil($customerLength / 18)
            );

            $sheet->getRowDimension($row)
                ->setRowHeight(max(39, 14 + 18 * $lines));

            // Keep city and road text horizontal, even in vertically merged cells.
            foreach (['B', 'C'] as $column) {
                $sheet->getStyle("{$column}{$row}")->getAlignment()
                    ->setTextRotation(0)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
            }

            $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
            $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
            $sheet->getStyle("G{$row}")->getNumberFormat()
->setFormatCode('General');

            $row++;
        }

        if ($rows === []) {
            $sheet->getRowDimension($row)->setRowHeight(50);
            $row++;
        }

        $summaryRow = $row;
        $sheet->getRowDimension($summaryRow)->setRowHeight(39);
        $sheet->getStyle("A{$summaryRow}:G{$summaryRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');
        $sheet->getStyle("A{$summaryRow}:G{$summaryRow}")->getFont()
            ->setBold(true);
        $sheet->getStyle("A{$summaryRow}:G{$summaryRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("G{$summaryRow}")->getNumberFormat()
            ->setFormatCode('#,##0.00');
    }

    private function addBlinkLogo(
        Worksheet $sheet,
        string $coordinate = 'G2'
    ): void
    {
$logoPath = public_path('images/blink-logo.png');
        if (!is_file($logoPath)) {
            throw new \RuntimeException("Blink export logo not found: {$logoPath}");
        }

        $logo = new Drawing();
        $logo->setName('Blink');
        $logo->setDescription('Blink logo');
        $logo->setPath($logoPath);
        $logo->setWidth(230);
        $logo->setCoordinates($coordinate);
        $logo->setOffsetX(5);
        $logo->setOffsetY(8);
        $logo->setWorksheet($sheet);
    }

    private function configurePrint(Worksheet $sheet, string $range): void
    {
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A3)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPrintArea($range);

        if (in_array($this->outdoorType, ['mural', 'rooftop', 'tunnel', 'bridge', 'unipole'], true)) {
            $headerRow = match ($this->outdoorType) {
                'tunnel' => self::TUNNEL_HEADER_ROW,
                'bridge' => self::BRIDGE_HEADER_ROW,
                'unipole' => self::UNIPOLE_HEADER_ROW,
                default => self::STANDARD_HEADER_ROW,
            };

            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(
                $headerRow,
                $headerRow
            );
        }
        $sheet->getPageMargins()
            ->setLeft(0.3)
            ->setRight(0.3)
            ->setTop(0.4)
            ->setBottom(0.4);
    }
}
