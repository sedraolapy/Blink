
@php
    $isArabic = app()->getLocale() === 'ar';
    $textAlign = $isArabic ? 'right' : 'left';

    $month = $report['month'];

    $isGrouped = in_array($outdoorType, [
        'tunnel',
        'bridge',
    ], true);

    $columns = match ($outdoorType) {
        'mural', 'rooftop' => [
            'city',
            'location',
            'customer',
            'width_m',
            'height_m',
            'total_area',
        ],

        'tunnel' => [
            'location',
            'customer',
            'dimensions',
            'total_area',
        ],

        'bridge' => [
            'location',
            'customer',
            'width_m',
            'height_m',
            'total_area',
        ],

        'unipole' => [
            'city',
            'connecting_road',
            'location',
            'customer',
            'dimensions',
            'total_area',
        ],
    };

    $columnCount = count($columns);

    $labels = [
        'city' => __('reports.institution_outdoor.city'),
        'connecting_road' => __('reports.institution_outdoor.connecting_road'),
        'location' => __('reports.institution_outdoor.location'),
        'customer' => __('reports.institution_outdoor.customer'),
        'width_m' => __('reports.institution_outdoor.width_m'),
        'height_m' => __('reports.institution_outdoor.height_m'),
        'dimensions' => __('reports.institution_outdoor.dimensions'),
        'total_area' => __('reports.institution_outdoor.total_area'),
    ];

    $groups = $isGrouped
        ? ($report['sections'] ?? [])
        : [
            [
                'items' => $report['items'] ?? [],
                'summary' => $report['summary'] ?? [
                    'total_area' => 0,
                ],
            ],
        ];

    $font = 'font-family:Arial,sans-serif;';

    $headerStyle = $font
        . 'background-color:#2F1660;'
        . 'color:#FFFFFF;'
        . 'border:1px solid #49347A;'
        . 'font-size:11pt;'
        . 'font-weight:bold;'
        . 'text-align:center;'
        . 'vertical-align:middle;';

    $bodyStyle = $font
        . 'border:1px solid #E8E1EB;'
        . 'font-size:10pt;'
        . 'color:#403548;'
        . 'vertical-align:middle;';

    $summaryStyle = $font
        . 'border:1px solid #E8E1EB;'
        . 'background-color:#F3ECF8;'
        . 'color:#2F1660;'
        . 'font-size:11pt;'
        . 'font-weight:bold;'
        . 'vertical-align:middle;';
@endphp

<table>
    {{-- Top spacing --}}
    <tr>
        <td colspan="{{ $columnCount }}">&nbsp;</td>
    </tr>

    {{-- Report title --}}
    <tr>
        <th colspan="{{ $columnCount }}"
            style="{{ $font }}color:#2F1660;font-size:20pt;font-weight:bold;text-align:center;">
            {{ __('reports.institution_outdoor.report_title') }}
        </th>
    </tr>

    {{-- Outdoor type --}}
    <tr>
        <th colspan="{{ $columnCount }}"
            style="{{ $font }}background-color:#F3ECF8;color:#2F1660;font-size:13pt;font-weight:bold;text-align:center;">
            {{ __('reports.institution_outdoor.outdoor_type') }}:
            {{ __("enums.external_asset_types.{$outdoorType}") }}
        </th>
    </tr>

    {{-- Selected month --}}
    <tr>
        <th colspan="{{ $columnCount }}"
            style="{{ $font }}background-color:#FAF6FD;color:#2F1660;font-size:12pt;text-align:center;border:1px solid #E6E0E9;">
            {{ __('reports.institution_outdoor.month') }}
            {{ $month['number'] }}
            -
            {{ $month['year'] }}
        </th>
    </tr>

    {{-- Display dates only --}}
    <tr>
        <td colspan="{{ $columnCount }}"
            style="{{ $font }}background-color:#FAF6FD;color:#574A60;font-size:11pt;text-align:center;border:1px solid #E6E0E9;">
            {{ __('reports.institution_outdoor.date_range') }}:
            {{ $from }}
            -
            {{ $to }}
        </td>
    </tr>

    <tr>
        <td colspan="{{ $columnCount }}">&nbsp;</td>
    </tr>

    {{-- Report groups --}}
    @forelse ($groups as $group)

        {{-- City heading for tunnels and bridges --}}
        @if ($isGrouped)
            <tr>
                <th colspan="{{ $columnCount }}"
                    style="{{ $font }}background-color:#40246B;color:#FFFFFF;font-size:13pt;font-weight:bold;text-align:{{ $textAlign }};border:1px solid #58427A;">
                    {{ $group['city']['name'] }}
                </th>
            </tr>
        @endif

        {{-- Column headers --}}
        <tr>
            @foreach ($columns as $column)
                <th style="{{ $headerStyle }}">
                    {{ $labels[$column] }}
                </th>
            @endforeach
        </tr>

        {{-- Items --}}
        @forelse ($group['items'] as $item)

            @php
                $background = $loop->even
                    ? '#FCFAFF'
                    : '#FFFFFF';
            @endphp

            <tr>
                @foreach ($columns as $column)

                    @php
                        $value = match ($column) {
                            'city' => $item['city']['name'] ?? '',
                            'customer' => $item['customer']['name'] ?? '',
                            default => $item[$column] ?? '',
                        };

                        $isNumeric = in_array($column, [
                            'width_m',
                            'height_m',
                            'total_area',
                        ], true);
                    @endphp

                    <td
                        data-type="{{ $isNumeric ? 'n' : 's' }}"
                        style="{{ $bodyStyle }}background-color:{{ $background }};text-align:{{ $isNumeric ? 'center' : $textAlign }};">
                        {{ $value }}
                    </td>

                @endforeach
            </tr>

        @empty
            <tr>
                <td colspan="{{ $columnCount }}"
                    style="{{ $bodyStyle }}text-align:center;color:#93899A;">
                    {{ __('reports.institution_outdoor.empty') }}
                </td>
            </tr>
        @endforelse

        {{-- City summary for tunnels and bridges --}}
        @if ($isGrouped)
            <tr>
                <th colspan="{{ $columnCount - 1 }}"
                    style="{{ $summaryStyle }}text-align:{{ $textAlign }};">
                    {{ $labels['total_area'] }}
                </th>

                <td data-type="n"
                    style="{{ $summaryStyle }}text-align:center;">
                    {{ $group['summary']['total_area'] }}
                </td>
            </tr>
        @endif

        <tr>
            <td colspan="{{ $columnCount }}">&nbsp;</td>
        </tr>

    @empty
        <tr>
            <td colspan="{{ $columnCount }}"
                style="{{ $bodyStyle }}text-align:center;color:#93899A;">
                {{ __('reports.institution_outdoor.empty') }}
            </td>
        </tr>
    @endforelse

    {{-- General summary for murals, rooftops, unipoles and tunnels --}}
    @if ($outdoorType !== 'bridge')
        <tr>
            <th colspan="{{ $columnCount - 1 }}"
                style="{{ $headerStyle }}text-align:{{ $textAlign }};">
                {{ $labels['total_area'] }}
            </th>

            <td data-type="n"
                style="{{ $headerStyle }}">
                {{ $report['summary']['total_area'] ?? 0 }}
            </td>
        </tr>
    @endif

</table>
