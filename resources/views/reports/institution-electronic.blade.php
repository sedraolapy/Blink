
@php
    $isArabic = app()->getLocale() === 'ar';
    $textAlign = $isArabic ? 'right' : 'left';

    $labels = [
        'title' => __('reports.institution_electronic.report_title'),
        'month' => __('reports.institution_electronic.month'),
        'dateRange' => __('reports.institution_electronic.date_range'),
        'location' => __('reports.institution_electronic.location'),
        'widthM' => __('reports.institution_electronic.width_m'),
        'widthPx' => __('reports.institution_electronic.width_px'),
        'heightM' => __('reports.institution_electronic.height_m'),
        'heightPx' => __('reports.institution_electronic.height_px'),
        'area' => __('reports.institution_electronic.area'),
        'totalArea' => __('reports.institution_electronic.total_area'),
        'empty' => __('reports.institution_electronic.empty'),
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
    {{-- Top spacing --}}
    <tr>
        <td colspan="6">&nbsp;</td>
    </tr>

    {{-- Report title --}}
    <tr>
        <th colspan="6"
            style="{{ $font }}color:#2F1660;font-size:20pt;font-weight:bold;text-align:center;">
            {{ $labels['title'] }}
        </th>
    </tr>

    {{-- Selected month and year --}}
    <tr>
        <th colspan="6"
            style="{{ $font }}background-color:#FAF6FD;color:#2F1660;font-size:12pt;text-align:center;border:1px solid #E6E0E9;">
            {{ $labels['month'] }}
            {{ $month['number'] }}
            -
            {{ $month['year'] }}
        </th>
    </tr>

    {{-- Dates displayed only in the title --}}
    <tr>
        <td colspan="6"
            style="{{ $font }}background-color:#FAF6FD;color:#574A60;font-size:11pt;text-align:center;border:1px solid #E6E0E9;">
            {{ $labels['dateRange'] }}:
            {{ $from }}
            -
            {{ $to }}
        </td>
    </tr>

    <tr>
        <td colspan="6">&nbsp;</td>
    </tr>

    {{-- City sections --}}
    @forelse ($sections as $section)

        {{-- City heading --}}
        <tr>
            <th colspan="6"
                style="{{ $font }}background-color:#40246B;color:#FFFFFF;font-size:13pt;font-weight:bold;text-align:{{ $textAlign }};border:1px solid #58427A;">
                {{ $section['city']['name'] }}
            </th>
        </tr>

        {{-- Column headings --}}
        <tr>
            <th style="{{ $headerStyle }}">
                {{ $labels['location'] }}
            </th>
            <th style="{{ $headerStyle }}">
                {{ $labels['widthM'] }}
            </th>
            <th style="{{ $headerStyle }}">
                {{ $labels['widthPx'] }}
            </th>
            <th style="{{ $headerStyle }}">
                {{ $labels['heightM'] }}
            </th>
            <th style="{{ $headerStyle }}">
                {{ $labels['heightPx'] }}
            </th>
            <th style="{{ $headerStyle }}">
                {{ $labels['area'] }}
            </th>
        </tr>

        {{-- Screens --}}
        @foreach ($section['items'] as $item)
            @php
                $background = $loop->even
                    ? '#FCFAFF'
                    : '#FFFFFF';
            @endphp

            <tr>
                <td data-type="s"
                    style="{{ $bodyStyle }}background-color:{{ $background }};text-align:{{ $textAlign }};">
                    {{ $item['location'] }}
                </td>

                <td data-type="n"
                    style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                    {{ $item['width_m'] }}
                </td>

                <td data-type="n"
                    style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                    {{ $item['width_px'] }}
                </td>

                <td data-type="n"
                    style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                    {{ $item['height_m'] }}
                </td>

                <td data-type="n"
                    style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                    {{ $item['height_px'] }}
                </td>

                <td data-type="n"
                    style="{{ $bodyStyle }}background-color:{{ $background }};text-align:center;">
                    {{ $item['area'] }}
                </td>
            </tr>
        @endforeach

        {{-- City total area --}}
        <tr>
            <th colspan="5"
                style="{{ $summaryStyle }}text-align:{{ $textAlign }};">
                {{ $labels['totalArea'] }}
            </th>

            <td data-type="n"
                style="{{ $summaryStyle }}text-align:center;">
                {{ $section['summary']['total_area'] }}
            </td>
        </tr>

        {{-- Space between cities --}}
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>

    @empty
        <tr>
            <td colspan="6"
                style="{{ $bodyStyle }}text-align:center;color:#93899A;">
                {{ $labels['empty'] }}
            </td>
        </tr>
    @endforelse
</table>
