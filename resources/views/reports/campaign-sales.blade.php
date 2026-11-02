@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = $isArabic
        ? [
            'title' => 'تقرير إجمالي مبيعات الحملة',
            'period' => 'الفترة',
            'statisticsBooked' => 'اللوحات المحجوزة',
            'statisticsConfirmed' => 'اللوحات المؤكدة',
            'statisticsUnconfirmed' => 'اللوحات غير المؤكدة',
            'statisticsRemaining' => 'المتبقي بعد المؤكد',
            'customer' => 'الشركة',
            'status' => 'حالة الحجز',
            'damascus' => 'لوحات دمشق',
            'otherGovernorates' => 'لوحات باقي المحافظات',
            'total' => 'المجموع',
            'confirmed' => 'مؤكد',
            'unconfirmed' => 'غير مؤكد',
            'inventory' => 'إجمالي المخزون',
            'booked' => 'اللوحات المحجوزة',
            'remaining' => 'المتبقي بعد المؤكد',
            'empty' => 'لا توجد حجوزات لهذه الفترة.',
        ]
        : [
            'title' => 'Campaign Sales Report',
            'period' => 'Period',
            'statisticsBooked' => 'Booked Billboards',
            'statisticsConfirmed' => 'Confirmed Billboards',
            'statisticsUnconfirmed' => 'Unconfirmed Billboards',
            'statisticsRemaining' => 'Remaining After Confirmed',
            'customer' => 'Company',
            'status' => 'Booking Status',
            'damascus' => 'Damascus Billboards',
            'otherGovernorates' => 'Other Governorates',
            'total' => 'Total',
            'confirmed' => 'Confirmed',
            'unconfirmed' => 'Unconfirmed',
            'inventory' => 'Total Inventory',
            'booked' => 'Booked Billboards',
            'remaining' => 'Remaining After Confirmed',
            'empty' => 'No bookings exist for this period.',
        ];

    // All counts and statuses are provided by Laravel. Grouping changes only
    // the display order to keep the same customer's status rows together.
    $customerGroups = collect($items)->groupBy(fn ($item) => $item['customer']['id']);

    $statistics = [
        ['label' => $labels['statisticsBooked'], 'value' => $summary['total']['booked_boards_count']],
        ['label' => $labels['statisticsConfirmed'], 'value' => $summary['total']['confirmed_boards_count']],
        ['label' => $labels['statisticsUnconfirmed'], 'value' => $summary['total']['unconfirmed_boards_count']],
        ['label' => $labels['statisticsRemaining'], 'value' => $summary['total']['remaining_boards_count']],
    ];

    $geographicRows = [
        ['label' => $labels['inventory'], 'key' => 'inventory_boards_count'],
        ['label' => $labels['booked'], 'key' => 'booked_boards_count'],
        ['label' => $labels['statisticsConfirmed'], 'key' => 'confirmed_boards_count'],
        ['label' => $labels['statisticsUnconfirmed'], 'key' => 'unconfirmed_boards_count'],
        ['label' => $labels['remaining'], 'key' => 'remaining_boards_count'],
    ];

    $textAlign = $isArabic ? 'right' : 'left';
    $font = 'font-family:Arial,sans-serif;';
    $border = 'border:1px solid #E8E1EB;';
    $headerStyle = $font . 'background-color:#2F1660;color:#FFFFFF;border:1px solid #49347A;font-size:12pt;font-weight:bold;text-align:center;vertical-align:middle;';
    $bodyStyle = $font . $border . 'font-size:11pt;vertical-align:middle;';
    $numberStyle = $bodyStyle . 'text-align:center;';
@endphp

{{-- One HTML table: necessary for reliable placement with FromView.
     Twelve underlying columns form four KPI blocks, five visible main-table
     columns, and two side-by-side geographic summary tables. --}}
<table>
    {{-- Row 1: top breathing room --}}
    <tr><td colspan="12">&nbsp;</td></tr>

    {{-- Row 2: title --}}
    <tr>
        <th colspan="12" style="{{ $font }}background-color:#FFFFFF;color:#2F1660;font-size:22pt;font-weight:bold;text-align:center;vertical-align:middle;">
            {{ $labels['title'] }}
        </th>
    </tr>

    {{-- Row 3: space --}}
    <tr><td colspan="12">&nbsp;</td></tr>

    {{-- Row 4: period panel (right) and dates (left) in RTL --}}
    <tr>
        <th colspan="6" style="{{ $font }}background-color:#FAF5FE;color:#2F1660;border:1px solid #E9DCED;font-size:13pt;font-weight:bold;text-align:{{ $textAlign }};vertical-align:middle;">
            {{ $labels['period'] }} {{ $period['number'] }} · {{ $period['year'] }}
        </th>
        <td colspan="6" data-type="s" style="{{ $font }}background-color:#FAF5FE;color:#2F1660;border:1px solid #E9DCED;font-size:11pt;font-weight:bold;text-align:center;vertical-align:middle;">
            {{ $period['start_date'] }} — {{ $period['end_date'] }}
        </td>
    </tr>

    {{-- Row 5: space --}}
    <tr><td colspan="12">&nbsp;</td></tr>

    {{-- Rows 6–7: four backend statistics, in preview order --}}
    <tr>
        @foreach ($statistics as $statistic)
            <th colspan="3" style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E6E0E9;font-size:11pt;font-weight:bold;text-align:center;vertical-align:middle;">
                {{ $statistic['label'] }}
            </th>
        @endforeach
    </tr>
    <tr>
        @foreach ($statistics as $statistic)
            <td colspan="3" data-type="n" style="{{ $font }}background-color:#FFFFFF;color:#2F1660;border:1px solid #E6E0E9;font-size:23pt;font-weight:bold;text-align:center;vertical-align:middle;">
                {{ $statistic['value'] }}
            </td>
        @endforeach
    </tr>

    {{-- Row 8: space --}}
    <tr><td colspan="12">&nbsp;</td></tr>

    {{-- Row 9: modest space before the table (no separate section title) --}}
    <tr><td colspan="12" style="background-color:#FFFFFF;border:none;">&nbsp;</td></tr>

    {{-- Row 10: five visual columns, matching the current frontend preview --}}
    <tr>
        <th colspan="4" style="{{ $headerStyle }}text-align:{{ $textAlign }};">{{ $labels['customer'] }}</th>
        <th style="{{ $headerStyle }}">{{ $labels['status'] }}</th>
        <th colspan="2" style="{{ $headerStyle }}">{{ $labels['damascus'] }}</th>
        <th colspan="3" style="{{ $headerStyle }}">{{ $labels['otherGovernorates'] }}</th>
        <th colspan="2" style="{{ $headerStyle }}background-color:#49267B;">{{ $labels['total'] }}</th>
    </tr>

    {{-- Rows 11+: one row per backend customer+status entry, grouped for rowspan --}}
    @forelse ($customerGroups as $customerRows)
        @php
            $groupBackground = $loop->odd ? '#FFFFFF' : '#FCFAFF';
        @endphp
        @foreach ($customerRows as $item)
            @php
                $status = $item['booking_status'];
                $statusBackground = $status === 'confirmed' ? '#F0E4FB' : '#F5F2F7';
                $statusColor = $status === 'confirmed' ? '#59227E' : '#75697D';
            @endphp
            <tr>
                @if ($loop->first)
                    <td colspan="4" rowspan="{{ $customerRows->count() }}" data-type="s" style="{{ $bodyStyle }}background-color:{{ $groupBackground }};color:#3E304B;font-weight:bold;text-align:{{ $textAlign }};vertical-align:middle;">
                        {{ $item['customer']['name'] }}
                    </td>
                @endif
                <td data-type="s" style="{{ $bodyStyle }}background-color:{{ $statusBackground }};color:{{ $statusColor }};font-weight:bold;text-align:center;">
                    {{ $labels[$status] }}
                </td>
                <td colspan="2" data-type="n" style="{{ $numberStyle }}background-color:{{ $groupBackground }};color:#574A60;">
                    {{ $item['damascus_boards_count'] }}
                </td>
                <td colspan="3" data-type="n" style="{{ $numberStyle }}background-color:{{ $groupBackground }};color:#574A60;">
                    {{ $item['other_governorates_boards_count'] }}
                </td>
                <td colspan="2" data-type="n" style="{{ $numberStyle }}background-color:#F8F1FD;color:#2F1660;font-weight:bold;">
                    {{ $item['total_boards_count'] }}
                </td>
            </tr>
        @endforeach
    @empty
        <tr>
            <td colspan="12" style="{{ $bodyStyle }}background-color:#FFFFFF;color:#93899A;text-align:center;">
                {{ $labels['empty'] }}
            </td>
        </tr>
    @endforelse

    {{-- Footer: no summation; only backend summary fields --}}
    <tr>
        <th colspan="5" style="{{ $headerStyle }}text-align:{{ $textAlign }};">{{ $labels['total'] }}</th>
        <td colspan="2" data-type="n" style="{{ $headerStyle }}">{{ $summary['damascus']['booked_boards_count'] }}</td>
        <td colspan="3" data-type="n" style="{{ $headerStyle }}">{{ $summary['other_governorates']['booked_boards_count'] }}</td>
        <td colspan="2" data-type="n" style="{{ $headerStyle }}background-color:#49267B;">{{ $summary['total']['booked_boards_count'] }}</td>
    </tr>

    {{-- Clear vertical separation between the main table and the area summaries --}}
    <tr><td colspan="12" style="background-color:#FFFFFF;border:none;">&nbsp;</td></tr>

    {{-- Two independent summaries with an empty F-column gutter. --}}
    <tr>
        <th colspan="5" style="{{ $headerStyle }}text-align:{{ $textAlign }};">{{ $labels['damascus'] }}</th>
        <td style="background-color:#FFFFFF;border:none;">&nbsp;</td>
        <th colspan="6" style="{{ $headerStyle }}text-align:{{ $textAlign }};">{{ $labels['otherGovernorates'] }}</th>
    </tr>
    @foreach ($geographicRows as $detail)
        @php
            $isRemaining = $detail['key'] === 'remaining_boards_count';
            $background = $isRemaining ? '#2F1660' : ($loop->odd ? '#FFFFFF' : '#FCFAFF');
            $color = $isRemaining ? '#FFFFFF' : '#574A60';
            $numberColor = $isRemaining ? '#FFFFFF' : '#2F1660';
        @endphp
        <tr>
            <th colspan="4" style="{{ $bodyStyle }}background-color:{{ $background }};color:{{ $color }};font-weight:{{ $isRemaining ? 'bold' : 'normal' }};text-align:{{ $textAlign }};">
                {{ $detail['label'] }}
            </th>
            <td data-type="n" style="{{ $numberStyle }}background-color:{{ $background }};color:{{ $numberColor }};font-weight:bold;">
                {{ $summary['damascus'][$detail['key']] }}
            </td>
            <td style="background-color:#FFFFFF;border:none;">&nbsp;</td>
            <th colspan="4" style="{{ $bodyStyle }}background-color:{{ $background }};color:{{ $color }};font-weight:{{ $isRemaining ? 'bold' : 'normal' }};text-align:{{ $textAlign }};">
                {{ $detail['label'] }}
            </th>
            <td colspan="2" data-type="n" style="{{ $numberStyle }}background-color:{{ $background }};color:{{ $numberColor }};font-weight:bold;">
                {{ $summary['other_governorates'][$detail['key']] }}
            </td>
        </tr>
    @endforeach
</table>
