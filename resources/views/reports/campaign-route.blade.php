@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = $isArabic
        ? [
            'title' => 'تقرير مسار الحملة',
            'period' => 'الفترة',
            'currentPeriod' => 'الفترة الحالية',
            'previousPeriod' => 'الفترة السابقة',
            'boards' => 'إجمالي اللوحات',
            'installation' => 'تركيب',
            'extension' => 'تمديد',
            'customers' => 'الشركات',
            'governorate' => 'المحافظة',
            'board' => 'اللوحة',
            'action' => 'الإجراء المطلوب',
            'noDesign' => 'لا يوجد تصميم',
            'empty' => 'لا توجد لوحات ضمن المحافظات المختارة في الفترتين.',
        ]
        : [
            'title' => 'Campaign Route Report',
            'period' => 'Period',
            'currentPeriod' => 'Current Period',
            'previousPeriod' => 'Previous Period',
            'boards' => 'Total Billboards',
            'installation' => 'Installation',
            'extension' => 'Extension',
            'customers' => 'Companies',
            'governorate' => 'Governorate',
            'board' => 'Billboard',
            'action' => 'Required Action',
            'noDesign' => 'No design',
            'empty' => 'No billboards found in the selected governorates for either period.',
        ];

    // Business counts and action types come from Laravel; nothing is recalculated here.
    $statistics = [
        ['label' => $labels['boards'], 'value' => $summary['boards_count']],
        ['label' => $labels['installation'], 'value' => $summary['installation_count']],
        ['label' => $labels['extension'], 'value' => $summary['extension_count']],
        ['label' => $labels['customers'], 'value' => $summary['customers_count']],
    ];

    $font = 'font-family:Arial,sans-serif;';
    $border = 'border:1px solid #E8E1EB;';
    $headerStyle = $font . 'background-color:#2F1660;color:#FFFFFF;border:1px solid #49347A;font-size:11pt;font-weight:bold;text-align:center;vertical-align:middle;';
    $cellStyle = $font . $border . 'font-size:10pt;vertical-align:middle;word-wrap:break-word;';
    $textAlign = $isArabic ? 'right' : 'left';

    $currentLabel = $labels['period'] . ' ' . $currentPeriod['number'] . ' · ' . $currentPeriod['year'];
    $previousLabel = $labels['period'] . ' ' . $previousPeriod['number'] . ' · ' . $previousPeriod['year'];
@endphp

{{-- One HTML table is required by maatwebsite/excel FromView.
     Twelve underlying columns let the four equal statistics and the five
     visible report columns share one properly aligned Excel worksheet. --}}
<table>
    {{-- Row 1: Top whitespace --}}
    <tr>
        <td colspan="12">&nbsp;</td>
    </tr>

    {{-- Row 2: Title --}}
    <tr>
        <th colspan="12"
            style="{{ $font }}background-color:#FFFFFF;color:#2F1660;font-size:20pt;font-weight:bold;text-align:center;vertical-align:middle;">
            {{ $labels['title'] }}
        </th>
    </tr>

    {{-- Row 3: Spacing --}}
    <tr>
        <td colspan="12">&nbsp;</td>
    </tr>

    {{-- Row 4: Current and previous periods. A:F / G:L are the two panels. --}}
    <tr>
        <th colspan="3" style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E6DCEA;font-size:12pt;font-weight:bold;text-align:{{ $textAlign }};vertical-align:middle;">
            {{ $labels['currentPeriod'] }}<br>{{ $currentLabel }}
        </th>
        <td colspan="3" data-type="s" style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E6DCEA;font-size:11pt;font-weight:bold;text-align:center;vertical-align:middle;">
            {{ $currentPeriod['start_date'] }} — {{ $currentPeriod['end_date'] }}
        </td>
        <th colspan="3" style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E6DCEA;font-size:12pt;font-weight:bold;text-align:{{ $textAlign }};vertical-align:middle;">
            {{ $labels['previousPeriod'] }}<br>{{ $previousLabel }}
        </th>
        <td colspan="3" data-type="s" style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E6DCEA;font-size:11pt;font-weight:bold;text-align:center;vertical-align:middle;">
            {{ $previousPeriod['start_date'] }} — {{ $previousPeriod['end_date'] }}
        </td>
    </tr>

    {{-- Rows 5–6: Compact spacing between the periods and statistics.
         Preserve row positions used by the Excel AfterSheet formatter. --}}
    <tr><td colspan="12">&nbsp;</td></tr>
    <tr><td colspan="12">&nbsp;</td></tr>

    {{-- Row 7: Statistic labels, equally sized like the Preview --}}
    <tr>
        @foreach ($statistics as $statistic)
            <th colspan="3"
                style="{{ $font }}background-color:#FAF6FD;color:#2F1660;border:1px solid #E6E0E9;font-size:11pt;font-weight:bold;text-align:center;vertical-align:middle;">
                {{ $statistic['label'] }}
            </th>
        @endforeach
    </tr>

    {{-- Row 8: Statistic values --}}
    <tr>
        @foreach ($statistics as $statistic)
            <td colspan="3" data-type="n"
                style="{{ $font }}background-color:#FFFFFF;color:#2F1660;border:1px solid #E6E0E9;font-size:22pt;font-weight:bold;text-align:center;vertical-align:middle;">
                {{ $statistic['value'] }}
            </td>
        @endforeach
    </tr>

    {{-- Row 9: Spacing --}}
    <tr>
        <td colspan="12">&nbsp;</td>
    </tr>

    {{-- Row 10: Five visual columns, with no serial-number column --}}
    <tr>
        <th colspan="2" style="{{ $headerStyle }}">{{ $labels['governorate'] }}</th>
        <th colspan="3" style="{{ $headerStyle }}">{{ $labels['board'] }}</th>
        <th colspan="3" style="{{ $headerStyle }}">{{ $labels['previousPeriod'] }} {{ $previousPeriod['number'] }}</th>
        <th colspan="3" style="{{ $headerStyle }}">{{ $labels['currentPeriod'] }} {{ $currentPeriod['number'] }}</th>
        <th style="{{ $headerStyle }}">{{ $labels['action'] }}</th>
    </tr>

    {{-- Rows 11+: Booking fields have been normalized by the Export class. --}}
    @forelse ($items as $item)
        @php
            $background = $loop->even ? '#FCFAFF' : '#FFFFFF';
            $action = $item['required_action'];
            $actionBackground = $action === 'installation'
                ? '#F0E4FB'
                : ($action === 'extension' ? '#E9E2F3' : $background);
            $actionColor = $action === 'installation' ? '#59227E' : '#47246A';
            $previousBooking = $item['previous_booking'];
            $currentBooking = $item['current_booking'];
        @endphp
        <tr>
            <td colspan="2" data-type="s"
                style="{{ $cellStyle }}background-color:{{ $background }};color:#574A60;text-align:{{ $textAlign }};font-weight:bold;">
                {{ $item['governorate']['name'] }}
            </td>

            <td colspan="3" data-type="s"
                style="{{ $cellStyle }}background-color:{{ $background }};color:#2F1660;text-align:{{ $textAlign }};font-weight:bold;">
                {{ $item['board']['name'] }}<br>{{ $item['board']['code'] }}
            </td>

            <td colspan="3" data-type="s"
                style="{{ $cellStyle }}background-color:{{ $background }};color:#3F3347;text-align:{{ $textAlign }};">
                @if ($previousBooking === null)
                    —
                @else
                    {{ $previousBooking['customer']['name'] }}<br>{{ $previousBooking['design_name'] ?? $labels['noDesign'] }}
                @endif
            </td>

            <td colspan="3" data-type="s"
                style="{{ $cellStyle }}background-color:{{ $background }};color:#3F3347;text-align:{{ $textAlign }};">
                @if ($currentBooking === null)
                    —
                @else
                    {{ $currentBooking['customer']['name'] }}<br>{{ $currentBooking['design_name'] ?? $labels['noDesign'] }}
                @endif
            </td>

            <td data-type="s"
                style="{{ $cellStyle }}background-color:{{ $actionBackground }};color:{{ $actionColor }};font-weight:bold;text-align:center;">
                @if ($action === 'installation' || $action === 'extension')
                    {{ $labels[$action] }}
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="12" style="{{ $cellStyle }}background-color:#FFFFFF;color:#93899A;text-align:center;">
                {{ $labels['empty'] }}
            </td>
        </tr>
    @endforelse
</table>
