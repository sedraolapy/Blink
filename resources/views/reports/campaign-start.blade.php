
@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = $isArabic ? [
        'title' => 'تقرير بداية الحملة',
        'period' => 'الفترة',
        'from' => 'من',
        'to' => 'إلى',
        'customer' => 'الشركة',
        'classification' => 'نوع الأمر',
        'networks' => 'شبكات',
        'boards' => 'لوحات',
        'total' => 'المجموع',
        'installation' => 'تركيب',
        'extension' => 'تمديد',
        'extension_total' => 'مجموع التمديد',
        'installation_total' => 'مجموع التركيب',
        'booked' => 'المحجوز',
        'inventory' => 'المخزون',
        'remaining' => 'المتبقي',
        'booked_boards' => 'اللوحات المحجوزة',
        'remaining_boards' => 'اللوحات المتبقية',
        'booked_networks' => 'الشبكات المحجوزة',
        'remaining_networks' => 'الشبكات المتبقية',
        'empty' => 'لا توجد أوامر تركيب أو تمديد صادرة لهذه الفترة',
        'note' => 'يختلف عدد اللوحات في الشبكة حسب المحافظة، مع التقريب للأعلى.',
    ] : [
        'title' => 'Campaign Start Report',
        'period' => 'Period',
        'from' => 'From',
        'to' => 'To',
        'customer' => 'Company',
        'classification' => 'Order Type',
        'networks' => 'Networks',
        'boards' => 'Boards',
        'total' => 'Total',
        'installation' => 'Installation',
        'extension' => 'Extension',
        'extension_total' => 'Total Extension',
        'installation_total' => 'Total Installation',
        'booked' => 'Booked',
        'inventory' => 'Inventory',
        'remaining' => 'Remaining',
        'booked_boards' => 'Booked Boards',
        'remaining_boards' => 'Remaining Boards',
        'booked_networks' => 'Booked Networks',
        'remaining_networks' => 'Remaining Networks',
        'empty' => 'No installation or extension orders have been issued',
        'note' => 'Network size varies by governorate. Network counts are rounded up.',
    ];

    $darkPurple = '#31185E';
    $brightPurple = '#8000FF';
    $lightPurple = '#F1E7FA';
    $borderColor = '#251139';

    $inventoryByGovernorate = collect(
        $summary['inventory']['governorates'] ?? []
    )->keyBy('governorate_id');

    $bookedByGovernorate = collect(
        $summary['booked']['governorates'] ?? []
    )->keyBy('governorate_id');

    // Display only governorates with inventory or issued orders.
    $displayGovernorates = collect($governorates)
        ->filter(function ($governorate) use (
            $inventoryByGovernorate,
            $bookedByGovernorate
        ) {
            $id = $governorate['id'];

            $inventory = $inventoryByGovernorate->get($id, []);
            $booked = $bookedByGovernorate->get($id, []);

            return (
                (int) ($inventory['boards_count'] ?? 0) > 0
                || (int) ($booked['boards_count'] ?? 0) > 0
            );
        })
        ->values();

    if ($displayGovernorates->isEmpty()) {
        $displayGovernorates = collect($governorates);
    }

    $columnCount = 4 + ($displayGovernorates->count() * 2);

    $kpiSpans = array_fill(
        0,
        4,
        intdiv($columnCount, 4)
    );

    for ($i = 0; $i < $columnCount % 4; $i++) {
        $kpiSpans[$i]++;
    }

    $bookedTotal = $summary['booked']['total'];
    $remainingTotal = $summary['remaining']['total'];

    $kpis = [
        [
            'label' => $labels['booked_boards'],
            'value' => $bookedTotal['boards_count'],
        ],
        [
            'label' => $labels['remaining_boards'],
            'value' => $remainingTotal['boards_count'],
        ],
        [
            'label' => $labels['booked_networks'],
            'value' => $bookedTotal['networks_count'],
        ],
        [
            'label' => $labels['remaining_networks'],
            'value' => $remainingTotal['networks_count'],
        ],
    ];

    // Extension rows first, then installation.
    $orderedItems = collect($items)
        ->sortBy(function ($item) {
            $order = $item['classification'] === 'extension'
                ? '0'
                : '1';

            return $order . '-' . $item['customer']['name'];
        })
        ->values();

    $summaryTypes = [
        'extension',
        'installation',
        'booked',
        'inventory',
        'remaining',
    ];

    $headerStyle =
        "background-color: {$darkPurple}; "
        . "color: #FFFFFF; "
        . "font-weight: bold; "
        . "text-align: center; "
        . "vertical-align: center; "
        . "border: 1px solid {$borderColor};";

    $cellStyle =
        "border: 1px solid {$borderColor}; "
        . "text-align: center; "
        . "vertical-align: center;";
@endphp

<table>

    <thead>

        {{-- Row 1: Top Spacing --}}
        <tr>
            <td colspan="{{ $columnCount }}" height="12"></td>
        </tr>

        {{-- Row 2: Report Title --}}
        <tr>
            <th
                colspan="{{ $columnCount }}"
                height="44"
                style="
                    color: {{ $darkPurple }};
                    font-size: 20px;
                    font-weight: bold;
                    text-align: center;
                    vertical-align: center;
                "
            >
                {{ $labels['title'] }}
            </th>
        </tr>

        {{-- Row 3: Spacing --}}
        <tr>
            <td colspan="{{ $columnCount }}" height="12"></td>
        </tr>

        {{-- Row 4: Period Information --}}
        <tr>
            <th
                colspan="{{ $columnCount }}"
                height="34"
                style="
                    background-color: {{ $lightPurple }};
                    color: {{ $darkPurple }};
                    font-weight: bold;
                    font-size: 12px;
                    text-align: center;
                    vertical-align: center;
                    border: 1px solid {{ $borderColor }};
                "
            >
                {{ $labels['period'] }} {{ $period['number'] }}
                -
                {{ $labels['from'] }} {{ $period['start_date'] }}
                {{ $labels['to'] }} {{ $period['end_date'] }}
            </th>
        </tr>

        {{-- Row 5: Spacing --}}
        <tr>
            <td colspan="{{ $columnCount }}" height="16"></td>
        </tr>

        {{-- Row 6: KPI Cards --}}
        <tr>
            @foreach($kpis as $kpi)
                <th
                    colspan="{{ $kpiSpans[$loop->index] }}"
                    height="43"
                    style="
                        background-color: {{ $brightPurple }};
                        color: #FFFFFF;
                        font-size: 12px;
                        font-weight: bold;
                        text-align: center;
                        vertical-align: center;
                        border: 1px solid {{ $borderColor }};
                    "
                >
                    {{ $kpi['label'] }}: {{ $kpi['value'] }}
                </th>
            @endforeach
        </tr>

        {{-- Row 7: Spacing --}}
        <tr>
            <td colspan="{{ $columnCount }}" height="18"></td>
        </tr>

        {{-- Row 8: Main Table Header --}}
        <tr>
            <th
                rowspan="2"
                width="36"
                style="{{ $headerStyle }}"
            >
                {{ $labels['customer'] }}
            </th>

            <th
                rowspan="2"
                width="18"
                style="{{ $headerStyle }}"
            >
                {{ $labels['classification'] }}
            </th>

            @foreach($displayGovernorates as $governorate)
                <th
                    colspan="2"
                    height="30"
                    style="{{ $headerStyle }}"
                >
                    {{ $governorate['name'] }}
                </th>
            @endforeach

            <th
                colspan="2"
                style="
                    {{ $headerStyle }}
                    background-color: {{ $brightPurple }};
                "
            >
                {{ $labels['total'] }}
            </th>
        </tr>

        {{-- Row 9: Networks / Boards --}}
        <tr>
            @foreach($displayGovernorates as $governorate)
                <th
                    width="12"
                    height="29"
                    style="{{ $headerStyle }}"
                >
                    {{ $labels['networks'] }}
                </th>

                <th
                    width="12"
                    style="{{ $headerStyle }}"
                >
                    {{ $labels['boards'] }}
                </th>
            @endforeach

            <th
                width="14"
                style="{{ $headerStyle }}"
            >
                {{ $labels['networks'] }}
            </th>

            <th
                width="14"
                style="{{ $headerStyle }}"
            >
                {{ $labels['boards'] }}
            </th>
        </tr>

    </thead>

    <tbody>

        {{-- Customer Rows --}}
        @forelse($orderedItems as $item)

            @php
                $countsByGovernorate = collect(
                    $item['governorates']
                )->keyBy('governorate_id');
            @endphp

            <tr>

                <td
                    height="27"
                    style="{{ $cellStyle }}"
                >
                    {{ $item['customer']['name'] }}
                </td>

                <td style="{{ $cellStyle }}">
                    {{
                        $labels[$item['classification']]
                        ?? $item['classification']
                    }}
                </td>

                @foreach($displayGovernorates as $governorate)

                    @php
                        $counts = $countsByGovernorate->get(
                            $governorate['id'],
                            []
                        );
                    @endphp

                    {{-- Networks --}}
                    <td
                        data-type="n"
                        style="{{ $cellStyle }}"
                    >
                        {{ $counts['networks_count'] ?? 0 }}
                    </td>

                    {{-- Boards --}}
                    <td
                        data-type="n"
                        style="{{ $cellStyle }}"
                    >
                        {{ $counts['boards_count'] ?? 0 }}
                    </td>

                @endforeach

                {{-- Total Networks --}}
                <td
                    data-type="n"
                    style="
                        {{ $cellStyle }}
                        background-color: {{ $lightPurple }};
                        font-weight: bold;
                    "
                >
                    {{ $item['total']['networks_count'] }}
                </td>

                {{-- Total Boards --}}
                <td
                    data-type="n"
                    style="
                        {{ $cellStyle }}
                        background-color: {{ $lightPurple }};
                        font-weight: bold;
                    "
                >
                    {{ $item['total']['boards_count'] }}
                </td>

            </tr>

        @empty

            <tr>
                <td
                    colspan="{{ $columnCount }}"
                    height="30"
                    style="
                        {{ $cellStyle }}
                        color: #666666;
                    "
                >
                    {{ $labels['empty'] }}
                </td>
            </tr>

        @endforelse

        {{-- Summary Rows --}}
        @foreach($summaryTypes as $type)

            @php
                $summaryRow = $summary[$type];

                $countsByGovernorate = collect(
                    $summaryRow['governorates']
                )->keyBy('governorate_id');

                $highlight = in_array(
                    $type,
                    ['booked', 'remaining'],
                    true
                );

                $rowBackground = $highlight
                    ? $brightPurple
                    : $lightPurple;

                $rowTextColor = $highlight
                    ? '#FFFFFF'
                    : $darkPurple;

                $rowStyle =
                    "background-color: {$rowBackground}; "
                    . "color: {$rowTextColor}; "
                    . "font-weight: bold;";
            @endphp

            <tr>

                {{-- Merge first two columns --}}
                <td
                    colspan="2"
                    height="29"
                    style="{{ $cellStyle }} {{ $rowStyle }}"
                >
                    {{
                        match($type) {
                            'extension' => $labels['extension_total'],
                            'installation' => $labels['installation_total'],
                            default => $labels[$type],
                        }
                    }}
                </td>

                @foreach($displayGovernorates as $governorate)

                    @php
                        $counts = $countsByGovernorate->get(
                            $governorate['id'],
                            []
                        );
                    @endphp

                    {{-- Networks --}}
                    <td
                        data-type="n"
                        style="{{ $cellStyle }} {{ $rowStyle }}"
                    >
                        {{ $counts['networks_count'] ?? 0 }}
                    </td>

                    {{-- Boards --}}
                    <td
                        data-type="n"
                        style="{{ $cellStyle }} {{ $rowStyle }}"
                    >
                        {{ $counts['boards_count'] ?? 0 }}
                    </td>

                @endforeach

                {{-- Total Networks --}}
                <td
                    data-type="n"
                    style="{{ $cellStyle }} {{ $rowStyle }}"
                >
                    {{ $summaryRow['total']['networks_count'] }}
                </td>

                {{-- Total Boards --}}
                <td
                    data-type="n"
                    style="{{ $cellStyle }} {{ $rowStyle }}"
                >
                    {{ $summaryRow['total']['boards_count'] }}
                </td>

            </tr>

        @endforeach

        {{-- Footer Note --}}
        <tr>
            <td
                colspan="{{ $columnCount }}"
                height="32"
                style="
                    background-color: {{ $lightPurple }};
                    color: {{ $darkPurple }};
                    font-weight: bold;
                    text-align: center;
                    vertical-align: center;
                    border: 1px solid {{ $borderColor }};
                "
            >
                {{ $labels['note'] }}
            </td>
        </tr>

    </tbody>

</table>
