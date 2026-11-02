@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = $isArabic
        ? [
            'title' => 'تقرير بداية الحملة',
            'period' => 'الفترة',
            'boards' => 'اللوحات',
            'networks' => 'الشبكات',
            'bookedBoards' => 'اللوحات المحجوزة',
            'remainingBoards' => 'اللوحات المتبقية',
            'bookedNetworks' => 'الشبكات المحجوزة',
            'remainingNetworks' => 'الشبكات المتبقية',
            'customer' => 'الشركة',
            'classification' => 'نوع العملية',
            'total' => 'المجموع',
            'installation' => 'تركيب',
            'extension' => 'تمديد',
            'booked' => 'المحجوز',
            'inventory' => 'المخزون',
            'remaining' => 'المتبقي',
            'extensionTotal' => 'مجموع التمديد',
            'installationTotal' => 'مجموع التركيب',
            'empty' => 'لا توجد أوامر تركيب أو تمديد لهذه الفترة.',
        ]
        : [
            'title' => 'Campaign Start Report',
            'period' => 'Period',
            'boards' => 'Billboards',
            'networks' => 'Networks',
            'bookedBoards' => 'Booked Billboards',
            'remainingBoards' => 'Remaining Billboards',
            'bookedNetworks' => 'Booked Networks',
            'remainingNetworks' => 'Remaining Networks',
            'customer' => 'Company',
            'classification' => 'Operation',
            'total' => 'Total',
            'installation' => 'Installation',
            'extension' => 'Extension',
            'booked' => 'Booked',
            'inventory' => 'Inventory',
            'remaining' => 'Remaining',
            'extensionTotal' => 'Total Extensions',
            'installationTotal' => 'Total Installations',
            'empty' => 'No installation or extension orders for this period.',
        ];

    $columnCount = count($governorates) * 2 + 4;
    $groupSpan = intdiv($columnCount, 2);
    $firstMetricSpan = (int) ceil($groupSpan / 2);
    $secondMetricSpan = $groupSpan - $firstMetricSpan;
    $textAlign = $isArabic ? 'right' : 'left';

    // The same grouping as the website: one merged company cell per customer.
    $companyGroups = collect($items)->groupBy(fn ($item) => $item['customer']['id']);

    $statGroups = [
        [
            'name' => $labels['boards'],
            'metrics' => [
                ['label' => $labels['bookedBoards'], 'value' => $summary['booked']['total']['boards_count']],
                ['label' => $labels['remainingBoards'], 'value' => $summary['remaining']['total']['boards_count']],
            ],
        ],
        [
            'name' => $labels['networks'],
            'metrics' => [
                ['label' => $labels['bookedNetworks'], 'value' => $summary['booked']['total']['networks_count']],
                ['label' => $labels['remainingNetworks'], 'value' => $summary['remaining']['total']['networks_count']],
            ],
        ],
    ];

    $summaryRows = [
        ['key' => 'extension', 'label' => $labels['extensionTotal'], 'background' => '#F7F3FA', 'color' => '#4B3561'],
        ['key' => 'installation', 'label' => $labels['installationTotal'], 'background' => '#F2EBF8', 'color' => '#4B3561'],
        ['key' => 'booked', 'label' => $labels['booked'], 'background' => '#EADCF8', 'color' => '#2F1660'],
        ['key' => 'inventory', 'label' => $labels['inventory'], 'background' => '#F7F3FA', 'color' => '#4B3561'],
        ['key' => 'remaining', 'label' => $labels['remaining'], 'background' => '#2F1660', 'color' => '#FFFFFF'],
    ];

    $font = 'font-family:Cairo,Arial,sans-serif;';
    $border = 'border:1px solid #E8E1EB;';
    $numberStyle = $font . $border . 'font-size:10pt;text-align:center;vertical-align:middle;';
    $headerStyle = $font . 'background-color:#2F1660;color:#FFFFFF;border:1px solid #58427A;font-size:10pt;font-weight:bold;text-align:center;vertical-align:middle;';
    $subheaderStyle = $font . 'background-color:#40246B;color:#FFFFFF;border:1px solid #58427A;font-size:9pt;font-weight:bold;text-align:center;vertical-align:middle;';
@endphp
<table>
    {{-- Row 1: Report title --}}
    <tr>
        <th colspan="{{ $columnCount }}" style="{{ $font }}background-color:#FFFFFF;color:#2F1660;font-size:19pt;font-weight:bold;text-align:center;vertical-align:middle;">
            {{ $labels['title'] }}
        </th>
    </tr>

    {{-- Row 2: Advertising period --}}
    <tr>
        <td colspan="{{ $columnCount }}" style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E9DCED;font-size:11pt;font-weight:bold;text-align:center;vertical-align:middle;">
            {{ $labels['period'] }} {{ $period['number'] }} · {{ $period['year'] }} | {{ $period['start_date'] }} — {{ $period['end_date'] }}
        </td>
    </tr>

    {{-- Row 3: Space --}}
    <tr><td colspan="{{ $columnCount }}">&nbsp;</td></tr>

    {{-- Row 4: Statistic group headings --}}
    <tr>
        @foreach ($statGroups as $group)
            <th colspan="{{ $groupSpan }}" style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E6DDEA;font-size:12pt;font-weight:bold;text-align:center;vertical-align:middle;">
                {{ $group['name'] }}
            </th>
        @endforeach
    </tr>

    {{-- Row 5: Statistic labels --}}
    <tr>
        @foreach ($statGroups as $group)
            @foreach ($group['metrics'] as $metric)
                <th colspan="{{ $loop->first ? $firstMetricSpan : $secondMetricSpan }}" style="{{ $font }}background-color:#FFFFFF;color:#827688;border:1px solid #EEE8F1;font-size:9pt;text-align:center;vertical-align:middle;">
                    {{ $metric['label'] }}
                </th>
            @endforeach
        @endforeach
    </tr>

    {{-- Row 6: Statistic values --}}
    <tr>
        @foreach ($statGroups as $group)
            @foreach ($group['metrics'] as $metric)
                <td colspan="{{ $loop->first ? $firstMetricSpan : $secondMetricSpan }}" data-type="n" style="{{ $font }}background-color:#FFFFFF;color:#2F1660;border:1px solid #EEE8F1;font-size:19pt;font-weight:bold;text-align:center;vertical-align:middle;">
                    {{ $metric['value'] }}
                </td>
            @endforeach
        @endforeach
    </tr>

    {{-- Row 7: Space --}}
    <tr><td colspan="{{ $columnCount }}">&nbsp;</td></tr>

    {{-- Rows 8–9: Two-level table header --}}
    <tr>
        <th rowspan="2" style="{{ $headerStyle }}text-align:{{ $textAlign }};">
            {{ $labels['customer'] }}
        </th>
        <th rowspan="2" style="{{ $headerStyle }}">
            {{ $labels['classification'] }}
        </th>
        @foreach ($governorates as $governorate)
            <th colspan="2" style="{{ $headerStyle }}" data-type="s">
                {{ $governorate['name'] }}
            </th>
        @endforeach
        <th colspan="2" style="{{ $headerStyle }}background-color:#49267B;">
            {{ $labels['total'] }}
        </th>
    </tr>
    <tr>
        @foreach ($governorates as $governorate)
            <th style="{{ $subheaderStyle }}">{{ $labels['boards'] }}</th>
            <th style="{{ $subheaderStyle }}">{{ $labels['networks'] }}</th>
        @endforeach
        <th style="{{ $subheaderStyle }}background-color:#573088;">{{ $labels['boards'] }}</th>
        <th style="{{ $subheaderStyle }}background-color:#573088;">{{ $labels['networks'] }}</th>
    </tr>

    {{-- Rows 10+: Company operations, grouped/merged like the preview --}}
    @forelse ($companyGroups as $customerItems)
        @php
            $rowBackground = $loop->even ? '#FCFAFF' : '#FFFFFF';
        @endphp

        @foreach ($customerItems as $item)
            @php
                $countsByGovernorate = collect($item['governorates'])->keyBy('governorate_id');
            @endphp
            <tr>
                @if ($loop->first)
                    <td rowspan="{{ $customerItems->count() }}" data-type="s" style="{{ $font }}{{ $border }}background-color:{{ $rowBackground }};color:#3E304B;font-size:10pt;font-weight:bold;text-align:{{ $textAlign }};vertical-align:middle;word-wrap:true;">
                        {{ $item['customer']['name'] }}
                    </td>
                @endif

                <td data-type="s" style="{{ $font }}{{ $border }}background-color:{{ $rowBackground }};color:#675277;font-size:10pt;text-align:center;vertical-align:middle;">
                    {{ $labels[$item['classification']] }}
                </td>

                @foreach ($governorates as $governorate)
                    @php
                        $counts = $countsByGovernorate[$governorate['id']];
                    @endphp
                    <td data-type="n" style="{{ $numberStyle }}background-color:{{ $rowBackground }};">{{ $counts['boards_count'] }}</td>
                    <td data-type="n" style="{{ $numberStyle }}background-color:{{ $rowBackground }};">{{ $counts['networks_count'] }}</td>
                @endforeach

                <td data-type="n" style="{{ $numberStyle }}background-color:#F5EBFF;color:#2F1660;font-weight:bold;">{{ $item['total']['boards_count'] }}</td>
                <td data-type="n" style="{{ $numberStyle }}background-color:#F5EBFF;color:#2F1660;font-weight:bold;">{{ $item['total']['networks_count'] }}</td>
            </tr>
        @endforeach
    @empty
        <tr>
            <td colspan="{{ $columnCount }}" style="{{ $font }}{{ $border }}background-color:#FFFFFF;color:#93899A;font-size:10pt;text-align:center;vertical-align:middle;">
                {{ $labels['empty'] }}
            </td>
        </tr>
    @endforelse

    {{-- Five backend-supplied totals: no frontend/Blade calculations --}}
    @foreach ($summaryRows as $row)
        @php
            $totals = $summary[$row['key']];
            $totalsByGovernorate = collect($totals['governorates'])->keyBy('governorate_id');
            $summaryStyle = $font . $border . 'background-color:' . $row['background'] . ';color:' . $row['color'] . ';font-size:10pt;font-weight:bold;vertical-align:middle;';
        @endphp
        <tr>
            <th colspan="2" style="{{ $summaryStyle }}text-align:{{ $textAlign }};">
                {{ $row['label'] }}
            </th>

            @foreach ($governorates as $governorate)
                @php
                    $counts = $totalsByGovernorate[$governorate['id']];
                @endphp
                <td data-type="n" style="{{ $summaryStyle }}text-align:center;">{{ $counts['boards_count'] }}</td>
                <td data-type="n" style="{{ $summaryStyle }}text-align:center;">{{ $counts['networks_count'] }}</td>
            @endforeach

            <td data-type="n" style="{{ $summaryStyle }}text-align:center;">{{ $totals['total']['boards_count'] }}</td>
            <td data-type="n" style="{{ $summaryStyle }}text-align:center;">{{ $totals['total']['networks_count'] }}</td>
        </tr>
    @endforeach
</table>
