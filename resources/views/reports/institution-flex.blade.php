
@php
    $isArabic = app()->getLocale() === 'ar';
    $textAlign = $isArabic ? 'right' : 'left';

    $labels = [
        'title' => __('reports.institution_flex.report_title'),
        'period' => __('reports.institution_flex.period'),
        'dateRange' => __('reports.institution_flex.date_range'),
        'customer' => __('reports.institution_flex.customer'),
        'dimensions' => __('reports.institution_flex.dimensions'),
        'boardsCount' => __('reports.institution_flex.boards_count'),
        'totalArea' => __('reports.institution_flex.total_area'),
        'booked' => $bookedLabel
            ?: __('reports.institution_flex.booked_boards_count'),
        'remaining' => $remainingLabel
            ?: __('reports.institution_flex.remaining_boards_count'),
        'empty' => __('reports.institution_flex.empty'),
    ];

    $font = 'font-family:Arial,sans-serif;';
    $border = 'border:1px solid #E8E1EB;';

    $headerStyle = $font
        . 'background-color:#2F1660;'
        . 'color:#FFFFFF;'
        . 'border:1px solid #49347A;'
        . 'font-size:11pt;'
        . 'font-weight:bold;'
        . 'text-align:center;'
        . 'vertical-align:middle;';

    $bodyStyle = $font
        . $border
        . 'font-size:10pt;'
        . 'color:#403548;'
        . 'vertical-align:middle;';

    $summaryStyle = $font
        . $border
        . 'background-color:#F3ECF8;'
        . 'color:#2F1660;'
        . 'font-size:11pt;'
        . 'font-weight:bold;'
        . 'vertical-align:middle;';
@endphp

<table>
    {{-- Report title --}}
    <tr>
        <td colspan="4">&nbsp;</td>
    </tr>

    <tr>
        <th colspan="4"
            style="{{ $font }}color:#2F1660;font-size:20pt;font-weight:bold;text-align:center;">
            {{ $labels['title'] }}
        </th>
    </tr>

    <tr>
        <th colspan="4"
            style="{{ $font }}background-color:#FAF6FD;color:#2F1660;font-size:12pt;text-align:center;border:1px solid #E6E0E9;">
            {{ $labels['period'] }}
            {{ $period['number'] }}
            -
            {{ $period['year'] }}
        </th>
    </tr>

    <tr>
        <td colspan="4"
            style="{{ $font }}background-color:#FAF6FD;color:#574A60;font-size:11pt;text-align:center;border:1px solid #E6E0E9;">
            {{ $labels['dateRange'] }}:
            {{ $from }}
            -
            {{ $to }}
        </td>
    </tr>

    <tr>
        <td colspan="4">&nbsp;</td>
    </tr>

    {{-- Governorate sections --}}
    @forelse ($sections as $section)

        {{-- Governorate and institution heading --}}
        <tr>
            <th colspan="4"
                style="{{ $font }}background-color:#40246B;color:#FFFFFF;font-size:13pt;font-weight:bold;text-align:{{ $textAlign }};border:1px solid #58427A;">
                {{ $section['governorate']['name'] }}
                -
                {{ $section['label'] }}
            </th>
        </tr>

        {{-- Table headers --}}
        <tr>
            <th style="{{ $headerStyle }}">
                {{ $labels['customer'] }}
            </th>

            <th style="{{ $headerStyle }}">
                {{ $labels['dimensions'] }}
            </th>

            <th style="{{ $headerStyle }}">
                {{ $labels['boardsCount'] }}
            </th>

            <th style="{{ $headerStyle }}">
                {{ $labels['totalArea'] }}
            </th>
        </tr>

        {{-- Customers and dimensions --}}
        @foreach ($section['customers'] as $customerRow)

            @foreach ($customerRow['items'] as $item)

                @php
                    $background = $loop->parent->even
                        ? '#FCFAFF'
                        : '#FFFFFF';
                @endphp

                <tr>
                    @if ($loop->first)
                        <td
                            rowspan="{{ count($customerRow['items']) }}"
                            data-type="s"
                            style="{{ $bodyStyle }}background-color:{{ $background }};font-weight:bold;text-align:{{ $textAlign }};">
                            {{ $customerRow['customer']['name'] }}
                        </td>
                    @endif

                    <td data-type="s"
                        style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                        {{ $item['dimensions'] }}
                    </td>

                    <td data-type="n"
                        style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                        {{ $item['boards_count'] }}
                    </td>

                    <td data-type="n"
                        style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                        {{ $item['total_area'] }}
                    </td>
                </tr>

            @endforeach

        @endforeach

        {{-- Booked summary --}}
        <tr>
            <th colspan="3"
                style="{{ $summaryStyle }}text-align:{{ $textAlign }};">
                {{ $labels['booked'] }}
            </th>

            <td data-type="n"
                style="{{ $summaryStyle }}text-align:center;">
                {{ $section['summary']['booked_boards_count'] }}
            </td>
        </tr>

        {{-- Remaining summary --}}
        <tr>
            <th colspan="3"
                style="{{ $summaryStyle }}text-align:{{ $textAlign }};">
                {{ $labels['remaining'] }}
            </th>

            <td data-type="n"
                style="{{ $summaryStyle }}text-align:center;">
                {{ $section['summary']['remaining_boards_count'] }}
            </td>
        </tr>

        {{-- Total area --}}
        <tr>
            <th colspan="3"
                style="{{ $headerStyle }}text-align:{{ $textAlign }};">
                {{ $labels['totalArea'] }}
            </th>

            <td data-type="n"
                style="{{ $headerStyle }}">
                {{ $section['summary']['total_area'] }}
            </td>
        </tr>

        {{-- Space between governorates --}}
        <tr>
            <td colspan="4">&nbsp;</td>
        </tr>

    @empty

        <tr>
            <td colspan="4"
                style="{{ $bodyStyle }}text-align:center;color:#93899A;">
                {{ $labels['empty'] }}
            </td>
        </tr>

    @endforelse
</table>
