
@php
    $isArabic = app()->getLocale() === 'ar';

    $t = $isArabic
        ? [
            'title' => 'تقرير إجمالي مبيعات الحملة',
            'period' => 'الفترة',
            'customer' => 'الشركة',
            'damascus_boards' => 'لوحات دمشق',
            'other_boards' => 'لوحات باقي المحافظات',
            'booked_boards' => 'اللوحات المحجوزة',
            'booking_status' => 'حالة الحجز',
            'confirmed' => 'مؤكد',
            'unconfirmed' => 'غير مؤكد',
            'remaining' => 'المتبقي بعد المؤكد',
            'total' => 'الإجمالي',
            'inventory' => 'إجمالي اللوحات',
            'damascus' => 'لوحات دمشق',
            'other_governorates' => 'لوحات باقي المحافظات',
            'empty' => 'لا توجد حجوزات لهذه الفترة',
            'note' => 'تُعرض الحجوزات غير المؤكدة بشكل مستقل، وتبقى ضمن المتبقي حتى تأكيدها.',
        ]
        : [
            'title' => 'Campaign Sales Report',
            'period' => 'Period',
            'customer' => 'Customer',
            'damascus_boards' => 'Damascus Boards',
            'other_boards' => 'Other Governorates Boards',
            'booked_boards' => 'Booked Boards',
            'booking_status' => 'Booking Status',
            'confirmed' => 'Confirmed',
            'unconfirmed' => 'Unconfirmed',
            'remaining' => 'Remaining After Confirmed',
            'total' => 'Total',
            'inventory' => 'Total Inventory',
            'damascus' => 'Damascus Boards',
            'other_governorates' => 'Other Governorates Boards',
            'empty' => 'No bookings for this period',
            'note' => 'Unconfirmed bookings are shown separately and remain in the available balance until confirmed.',
        ];

    $purple = '#32185F';
    $brightPurple = '#8000FF';
    $lightPurple = '#F2E7F8';
    $softPurple = '#E9D8F3';
    $borderColor = '#DCCBE8';

    $baseStyle =
        'font-family:Arial,sans-serif;'
        . 'text-align:center;'
        . 'vertical-align:middle;'
        . 'word-wrap:break-word;';

    $headerStyle = $baseStyle
        . 'background-color:' . $purple . ';'
        . 'color:#FFFFFF;'
        . 'font-size:12pt;'
        . 'font-weight:bold;'
        . 'border:1px solid ' . $borderColor . ';';

    $kpiStyle = $baseStyle
        . 'background-color:' . $softPurple . ';'
        . 'color:' . $purple . ';'
        . 'font-size:12pt;'
        . 'font-weight:bold;'
        . 'border:1px solid ' . $borderColor . ';';

    $summaryCellStyle = $baseStyle
        . 'background-color:#F9F5FC;'
        . 'color:' . $purple . ';'
        . 'font-size:11pt;'
        . 'font-weight:bold;'
        . 'border:1px solid ' . $borderColor . ';';

    $remainingStyle = $baseStyle
        . 'background-color:' . $softPurple . ';'
        . 'color:' . $purple . ';'
        . 'font-size:11pt;'
        . 'font-weight:bold;'
        . 'border:1px solid ' . $borderColor . ';';

    $periodText = $t['period']
        . ' ' . $period['number']
        . ' (' . $period['start_date']
        . ' — ' . $period['end_date'] . ')';

    $groupedItems = collect($items)->groupBy(
        fn (array $item) => $item['customer']->id
    );

    $summaryRows = [
        'inventory_boards_count' => $t['inventory'],
        'confirmed_boards_count' => $t['confirmed'],
        'unconfirmed_boards_count' => $t['unconfirmed'],
        'remaining_boards_count' => $t['remaining'],
    ];
@endphp

<table
    dir="{{ $isArabic ? 'rtl' : 'ltr' }}"
    style="border-collapse:collapse;font-family:Arial,sans-serif;"
>

    {{-- Row 1: Spacing --}}
    <tr>
        <td colspan="5" height="18">&nbsp;</td>
    </tr>

    {{-- Row 2: Report title --}}
    <tr>
        <td
            colspan="5"
            height="50"
            align="center"
            style="{{ $baseStyle }}
                color:{{ $purple }};
                font-size:24pt;
                font-weight:bold;"
        >
            {{ $t['title'] }}
        </td>
    </tr>

    {{-- Row 3: Spacing --}}
    <tr>
        <td colspan="5" height="18">&nbsp;</td>
    </tr>

    {{-- Row 4: Current period --}}
    <tr>
        <td
            colspan="5"
            height="34"
            align="center"
            style="{{ $baseStyle }}
                background-color:{{ $lightPurple }};
                color:{{ $purple }};
                font-size:12pt;
                font-weight:bold;
                border:1px solid {{ $borderColor }};"
        >
            {{ $periodText }}
        </td>
    </tr>

    {{-- Row 5: Spacing --}}
    <tr>
        <td colspan="5" height="20">&nbsp;</td>
    </tr>

    {{-- Row 6: KPI Summary --}}
    <tr>
        <td
            colspan="2"
            height="42"
            align="center"
            style="{{ $kpiStyle }}"
        >
            {{ $t['booked_boards'] }}:
            {{ $summary['total']['booked_boards_count'] }}
        </td>

        <td
            align="center"
            style="{{ $kpiStyle }}"
        >
            {{ $t['confirmed'] }}:
            {{ $summary['total']['confirmed_boards_count'] }}
        </td>

        <td
            align="center"
            style="{{ $kpiStyle }}"
        >
            {{ $t['unconfirmed'] }}:
            {{ $summary['total']['unconfirmed_boards_count'] }}
        </td>

        <td
            align="center"
            style="{{ $kpiStyle }}"
        >
            {{ $t['remaining'] }}:
            {{ $summary['total']['remaining_boards_count'] }}
        </td>
    </tr>

    {{-- Row 7: Spacing --}}
    <tr>
        <td colspan="5" height="20">&nbsp;</td>
    </tr>

    {{-- Row 8: Main table header --}}
    <tr>
        <th height="44" style="{{ $headerStyle }}">
            {{ $t['customer'] }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ $t['damascus_boards'] }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ $t['other_boards'] }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ $t['booked_boards'] }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ $t['booking_status'] }}
        </th>
    </tr>

    {{-- Row 9 onwards: Customer bookings --}}
    @forelse ($groupedItems as $customerRows)

        @php
            $groupBackground = $loop->even
                ? '#F9F5FC'
                : '#FFFFFF';

            $cellStyle = $baseStyle
                . 'background-color:' . $groupBackground . ';'
                . 'color:#30223E;'
                . 'font-size:11pt;'
                . 'border:1px solid ' . $borderColor . ';';

            $customerStyle = $cellStyle
                . 'font-weight:bold;';

            $statusStyle = $baseStyle
                . 'background-color:' . $lightPurple . ';'
                . 'color:' . $purple . ';'
                . 'font-size:11pt;'
                . 'font-weight:bold;'
                . 'border:1px solid ' . $borderColor . ';';
        @endphp

        @foreach ($customerRows as $item)

            <tr>

                {{-- Merge customer name when it has
                     confirmed and unconfirmed rows --}}
                @if ($loop->first)
                    <td
                        rowspan="{{ $customerRows->count() }}"
                        height="34"
                        data-type="s"
                        align="center"
                        style="{{ $customerStyle }}"
                    >
                        {{ $item['customer']->name }}
                    </td>
                @endif

                {{-- Damascus boards --}}
                <td
                    data-type="n"
                    align="center"
                    style="{{ $cellStyle }}"
                >
                    {{ $item['damascus_boards_count'] }}
                </td>

                {{-- Other governorates boards --}}
                <td
                    data-type="n"
                    align="center"
                    style="{{ $cellStyle }}"
                >
                    {{ $item['other_governorates_boards_count'] }}
                </td>

                {{-- Total booked boards --}}
                <td
                    data-type="n"
                    align="center"
                    style="{{ $cellStyle }}
                        font-weight:bold;"
                >
                    {{ $item['total_boards_count'] }}
                </td>

                {{-- Booking status --}}
                <td
                    data-type="s"
                    align="center"
                    style="{{ $statusStyle }}"
                >
                    {{
                        $item['booking_status'] === 'confirmed'
                            ? $t['confirmed']
                            : $t['unconfirmed']
                    }}
                </td>

            </tr>

        @endforeach

    @empty

        <tr>
            <td
                colspan="5"
                height="40"
                align="center"
                style="{{ $summaryCellStyle }}"
            >
                {{ $t['empty'] }}
            </td>
        </tr>

    @endforelse

    {{-- Main table totals --}}
    <tr>
        <td
            height="36"
            align="center"
            style="{{ $headerStyle }}
                background-color:{{ $brightPurple }};"
        >
            {{ $t['total'] }}
        </td>

        <td
            data-type="n"
            align="center"
            style="{{ $remainingStyle }}"
        >
            {{ $summary['damascus']['booked_boards_count'] }}
        </td>

        <td
            data-type="n"
            align="center"
            style="{{ $remainingStyle }}"
        >
            {{ $summary['other_governorates']['booked_boards_count'] }}
        </td>

        <td
            data-type="n"
            align="center"
            style="{{ $remainingStyle }}"
        >
            {{ $summary['total']['booked_boards_count'] }}
        </td>

        <td>&nbsp;</td>
    </tr>

    {{-- Spacing before summaries --}}
    <tr>
        <td colspan="5" height="18">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="5" height="18">&nbsp;</td>
    </tr>

    {{-- Summary section headers --}}
    <tr>
        <th
            colspan="2"
            height="36"
            style="{{ $headerStyle }}"
        >
            {{ $t['damascus'] }}
        </th>

        <td>&nbsp;</td>

        <th
            colspan="2"
            style="{{ $headerStyle }}"
        >
            {{ $t['other_governorates'] }}
        </th>
    </tr>

    {{-- Inventory / Confirmed / Unconfirmed / Remaining --}}
    @foreach ($summaryRows as $key => $label)

        @php
            $rowStyle = $key === 'remaining_boards_count'
                ? $remainingStyle
                : $summaryCellStyle;
        @endphp

        <tr>

            {{-- Damascus label --}}
            <td
                height="31"
                align="center"
                style="{{ $rowStyle }}"
            >
                {{ $label }}
            </td>

            {{-- Damascus value --}}
            <td
                data-type="n"
                align="center"
                style="{{ $rowStyle }}"
            >
                {{ $summary['damascus'][$key] }}
            </td>

            {{-- Separator --}}
            <td>&nbsp;</td>

            {{-- Other governorates label --}}
            <td
                align="center"
                style="{{ $rowStyle }}"
            >
                {{ $label }}
            </td>

            {{-- Other governorates value --}}
            <td
                data-type="n"
                align="center"
                style="{{ $rowStyle }}"
            >
                {{ $summary['other_governorates'][$key] }}
            </td>

        </tr>

    @endforeach

    {{-- Spacing --}}
    <tr>
        <td colspan="5" height="18">&nbsp;</td>
    </tr>

    {{-- Footer note --}}
    <tr>
        <td
            colspan="5"
            height="40"
            align="center"
            style="{{ $baseStyle }}
                background-color:{{ $lightPurple }};
                color:{{ $purple }};
                font-size:11pt;
                font-weight:bold;
                border:1px solid {{ $borderColor }};"
        >
            {{ $t['note'] }}
        </td>
    </tr>

</table>
