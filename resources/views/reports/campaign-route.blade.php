
@php
    $isArabic = app()->getLocale() === 'ar';

    $t = $isArabic
        ? [
            'title' => 'تقرير مسار الحملة',
            'previous' => 'الفترة السابقة',
            'current' => 'الفترة الحالية',
            'total_boards' => 'اللوحات إجمالي',
            'installation' => 'تركيب',
            'extension' => 'تمديد',
            'no_order' => 'لا يوجد أمر',
            'no_booking' => '—',
            'no_design' => 'بدون تصميم',
            'number' => 'الرقم',
            'governorate' => 'المحافظة',
            'board' => 'اللوحة',
            'status' => 'الحالة',
            'total' => 'إجمالي',
            'customers' => 'الشركات',
            'empty' => 'لا توجد حجوزات ضمن المحافظات المحددة',
        ]
        : [
            'title' => 'Campaign Route Report',
            'previous' => 'Previous period',
            'current' => 'Current period',
            'total_boards' => 'Total boards',
            'installation' => 'Installation',
            'extension' => 'Extension',
            'no_order' => 'No order',
            'no_booking' => '—',
            'no_design' => 'No design',
            'number' => 'No.',
            'governorate' => 'Governorate',
            'board' => 'Billboard',
            'status' => 'Status',
            'total' => 'Total',
            'customers' => 'Customers',
            'empty' => 'No bookings for the selected governorates',
        ];

    $purple = '#32185F';
    $lightPurple = '#F2E7F8';
    $softPurple = '#E9D8F3';
    $borderColor = '#DCCBE8';
    $white = '#FFFFFF';

    $baseStyle = 'font-family:Arial,sans-serif;'
        . 'text-align:center;'
        . 'vertical-align:middle;'
        . 'word-wrap:break-word;';

    $headerStyle = $baseStyle
        . 'background-color:' . $purple . ';'
        . 'color:#FFFFFF;'
        . 'font-size:13pt;'
        . 'font-weight:bold;'
        . 'border:1px solid ' . $borderColor . ';';

    $previousText = $t['previous']
        . ' ' . $previousPeriod['number']
        . ' (' . $previousPeriod['start_date']
        . ' — ' . $previousPeriod['end_date'] . ')';

    $currentText = $t['current']
        . ' ' . $currentPeriod['number']
        . ' (' . $currentPeriod['start_date']
        . ' — ' . $currentPeriod['end_date'] . ')';
@endphp

<table
    dir="{{ $isArabic ? 'rtl' : 'ltr' }}"
    style="border-collapse:collapse;font-family:Arial,sans-serif;"
>
    {{-- Row 1: Space --}}
    <tr>
        <td colspan="6" height="18">&nbsp;</td>
    </tr>

    {{-- Row 2: Report title --}}
    <tr>
        <td
            colspan="6"
            height="50"
            align="center"
            valign="center"
            style="{{ $baseStyle }}
                color:{{ $purple }};
                font-size:24pt;
                font-weight:bold;"
        >
            {{ $t['title'] }}
        </td>
    </tr>

    {{-- Row 3: Space --}}
    <tr>
        <td colspan="6" height="18">&nbsp;</td>
    </tr>

    {{-- Row 4: Periods --}}
    <tr>
        <td
            colspan="6"
            height="34"
            align="center"
            style="{{ $baseStyle }}
                background-color:{{ $lightPurple }};
                color:{{ $purple }};
                font-size:12pt;
                font-weight:bold;
                border:1px solid {{ $borderColor }};"
        >
            {{ $currentText }}
            |
            {{ $previousText }}
        </td>
    </tr>

    {{-- Row 5: Space --}}
    <tr>
        <td colspan="6" height="20">&nbsp;</td>
    </tr>

    {{-- Row 6: Summary --}}
    <tr>
        <td
            colspan="2"
            height="40"
            align="center"
            style="{{ $baseStyle }}
                background-color:{{ $lightPurple }};
                color:{{ $purple }};
                font-size:13pt;
                font-weight:bold;
                border:1px solid {{ $borderColor }};"
        >
            {{ $t['total_boards'] }}:
            {{ $summary['boards_count'] }}
        </td>

        <td
            colspan="2"
            height="40"
            align="center"
            style="{{ $baseStyle }}
                background-color:{{ $lightPurple }};
                color:{{ $purple }};
                font-size:13pt;
                font-weight:bold;
                border:1px solid {{ $borderColor }};"
        >
            {{ $t['extension'] }}:
            {{ $summary['extension_count'] }}
        </td>

        <td
            colspan="2"
            height="40"
            align="center"
            style="{{ $baseStyle }}
                background-color:{{ $lightPurple }};
                color:{{ $purple }};
                font-size:13pt;
                font-weight:bold;
                border:1px solid {{ $borderColor }};"
        >
            {{ $t['installation'] }}:
            {{ $summary['installation_count'] }}
        </td>
    </tr>

    {{-- Row 7: Space --}}
    <tr>
        <td colspan="6" height="20">&nbsp;</td>
    </tr>

    {{-- Row 8: Table header --}}
    <tr>
        <th width="65" height="44" style="{{ $headerStyle }}">
            {{ $t['number'] }}
        </th>

        <th width="150" style="{{ $headerStyle }}">
            {{ $t['governorate'] }}
        </th>

        <th width="320" style="{{ $headerStyle }}">
            {{ $t['board'] }}
        </th>

        <th width="320" style="{{ $headerStyle }}">
            {{ $t['previous'] }}
            {{ $previousPeriod['number'] }}
        </th>

        <th width="320" style="{{ $headerStyle }}">
            {{ $t['current'] }}
            {{ $currentPeriod['number'] }}
        </th>

        <th width="150" style="{{ $headerStyle }}">
            {{ $t['status'] }}
        </th>
    </tr>

    {{-- Row 9 onwards: Report items --}}
    @forelse ($items as $item)
        @php
            $previous = $item['previous_item'];
            $current = $item['current_item'];

            $previousCustomer = $previous
                ?->period
                ?->flexBooking
                ?->booking
                ?->customer;

            $currentCustomer = $current
                ?->period
                ?->flexBooking
                ?->booking
                ?->customer;

            $previousDesign = $previous?->design?->name;
            $currentDesign = $current?->design?->name;

            $action = match ($item['required_action']) {
                'installation' => $t['installation'],
                'extension' => $t['extension'],
                default => $t['no_order'],
            };

            $background = $loop->even
                ? '#F9F5FC'
                : '#FFFFFF';

            $cellStyle = $baseStyle
                . 'background-color:' . $background . ';'
                . 'color:#30223E;'
                . 'font-size:11pt;'
                . 'border:1px solid ' . $borderColor . ';';

            $highlightStyle = $baseStyle
                . 'background-color:' . $lightPurple . ';'
                . 'color:' . $purple . ';'
                . 'font-size:11pt;'
                . 'font-weight:bold;'
                . 'border:1px solid ' . $borderColor . ';';
        @endphp

        <tr>
            {{-- Number --}}
            <td
                height="64"
                align="center"
                style="{{ $highlightStyle }}"
            >
                {{ $loop->iteration }}
            </td>

            {{-- Governorate --}}
            <td
                data-type="s"
                align="center"
                style="{{ $cellStyle }}"
            >
                {{ $item['governorate']?->name ?? '—' }}
            </td>

            {{-- Billboard --}}
            <td
                data-type="s"
                align="center"
                style="{{ $cellStyle }}"
            >
                {{ $item['board']->location_name }}
            </td>

            {{-- Previous period booking --}}
            <td
                data-type="s"
                align="center"
                style="{{ $cellStyle }}"
            >
                @if ($previous)
                    <strong>
                        {{ $previousCustomer?->name ?? '—' }}
                    </strong>
                    <br>
                    {{ $previousDesign ?? $t['no_design'] }}
                @else
                    {{ $t['no_booking'] }}
                @endif
            </td>

            {{-- Current period booking --}}
            <td
                data-type="s"
                align="center"
                style="{{ $cellStyle }}"
            >
                @if ($current)
                    <strong>
                        {{ $currentCustomer?->name ?? '—' }}
                    </strong>
                    <br>
                    {{ $currentDesign ?? $t['no_design'] }}
                @else
                    {{ $t['no_booking'] }}
                @endif
            </td>

            {{-- Required action --}}
            <td
                data-type="s"
                align="center"
                style="{{ $highlightStyle }}"
            >
                {{ $action }}
            </td>
        </tr>
    @empty
        <tr>
            <td
                colspan="6"
                height="50"
                align="center"
                style="{{ $baseStyle }}
                    background-color:#F9F5FC;
                    color:{{ $purple }};
                    border:1px solid {{ $borderColor }};"
            >
                {{ $t['empty'] }}
            </td>
        </tr>
    @endforelse

    {{-- Footer: Totals --}}
    <tr>
        <td
            height="36"
            align="center"
            style="{{ $headerStyle }}
                background-color:#8000FF;"
        >
            {{ $t['total'] }}
        </td>

        <td
            align="center"
            style="{{ $highlightStyle }}"
        >
            {{ $summary['boards_count'] }}
        </td>

        <td
            style="{{ $cellStyle ?? $baseStyle }}"
        >
            &nbsp;
        </td>

        <td
            align="center"
            style="{{ $headerStyle }}
                background-color:#8000FF;"
        >
            {{ $t['customers'] }}
        </td>

        <td
            align="center"
            style="{{ $highlightStyle }}"
        >
            {{ $summary['customers_count'] }}
        </td>

        <td
            style="{{ $cellStyle ?? $baseStyle }}"
        >
            &nbsp;
        </td>
    </tr>
</table>
