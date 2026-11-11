@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = $isArabic
        ? [
            'intro' => 'مقدمة : شركة بلينك للخدمات الإعلامية - 9070-011 / دمشق - سورية',
            'recipient' => 'السادة المؤسسة العربية للإعلان',
            'greeting' => 'تحية وبعد',
            'sentenceStart' => 'نرفق لكم جدول لوحات اليونيبول خلال الفترة من تاريخ',
            'sentenceUntil' => 'حتى',
            'city' => 'المدينة',
            'connectingRoad' => 'الطريق الواصل',
            'location' => 'لوحات يونيبول',
            'material' => 'المادة الإعلانية',
            'dimensions' => 'القياس',
            'area' => 'إجمالي المساحة',
            'totalLabel' => 'الإجمالي لليونيبول',
            'empty' => 'لا توجد بيانات ضمن الشهر المحدد.',
        ]
        : [
            'intro' => 'Introduction: Blink Media Services Company - 9070-011 / Damascus - Syria',
            'recipient' => 'To the Arab Advertising Foundation',
            'greeting' => 'Greetings,',
            'sentenceStart' => 'Attached is the unipole billboard schedule for the period from',
            'sentenceUntil' => 'to',
            'city' => 'City',
            'connectingRoad' => 'Connecting Road',
            'location' => 'Unipole Billboards',
            'material' => 'Advertising Material',
            'dimensions' => 'Dimensions',
            'area' => 'Total Area',
            'totalLabel' => 'Total Unipole Area',
            'empty' => 'No data available for the selected month.',
        ];

    $formatDocDate = static function (string $date): string {
        [$year, $month, $day] = explode('-', $date);

        return $year . ' / ' . (int) $month . ' / ' . (int) $day;
    };

    $align = $isArabic ? 'right' : 'left';
    $font = 'font-family:Calibri,sans-serif;';
    $border = 'border:1px solid #4A4A4A;';

    $introStyle = $font
        . 'font-size:16pt;font-weight:bold;color:#161117;'
        . 'background-color:#FFFFFF;text-align:' . $align . ';vertical-align:middle;';

    $sentenceStyle = $font
        . 'font-size:12pt;font-weight:bold;color:#161117;'
        . 'background-color:#FFFFFF;text-align:' . $align . ';vertical-align:middle;';

    $columnHeaderStyle = $font . $border
        . 'font-size:10.5pt;font-weight:bold;color:#161117;'
        . 'background-color:#DEDEDE;text-align:center;vertical-align:middle;';

    $cellStyle = $font . $border
        . 'font-size:12pt;color:#161117;background-color:#FFFFFF;'
        . 'text-align:center;vertical-align:middle;';

    $summaryStyle = $font . $border
        . 'font-size:12pt;font-weight:bold;color:#161117;'
        . 'background-color:#BFBFBF;text-align:center;vertical-align:middle;';

    $rows = $unipoleRows;
@endphp

<table>
    <tr>
        <td colspan="5" data-type="s" style="{{ $introStyle }}">{{ $labels['intro'] }}</td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="5" data-type="s" style="{{ $introStyle }}">{{ $labels['recipient'] }}</td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="5" data-type="s" style="{{ $introStyle }}">{{ $labels['greeting'] }}</td>
        <td colspan="2">&nbsp;</td>
    </tr>

    <tr><td colspan="7">&nbsp;</td></tr>
    <tr><td colspan="7">&nbsp;</td></tr>

    <tr>
        <td colspan="7" data-type="s" style="{{ $sentenceStyle }}">
            {{ $labels['sentenceStart'] }}
            {{ $formatDocDate($from) }}
            {{ $labels['sentenceUntil'] }}
            {{ $formatDocDate($to) }}
        </td>
    </tr>
    <tr><td colspan="7">&nbsp;</td></tr>

    <tr>
        <th style="{{ $columnHeaderStyle }}">#</th>
        <th style="{{ $columnHeaderStyle }}">{{ $labels['city'] }}</th>
        <th style="{{ $columnHeaderStyle }}">{{ $labels['connectingRoad'] }}</th>
        <th style="{{ $columnHeaderStyle }}">{{ $labels['location'] }}</th>
        <th style="{{ $columnHeaderStyle }}">{{ $labels['material'] }}</th>
        <th style="{{ $columnHeaderStyle }}">{{ $labels['dimensions'] }}</th>
        <th style="{{ $columnHeaderStyle }}">{{ $labels['area'] }}</th>
    </tr>

    @forelse ($rows as $row)
        @php
            $item = $row['item'];
        @endphp
        <tr>
            <td data-type="n" style="{{ $cellStyle }}font-weight:bold;">{{ $loop->iteration }}</td>

            @if ($row['city_span'] > 0)
                <td rowspan="{{ $row['city_span'] }}" data-type="s" style="{{ $cellStyle }}font-weight:bold;">
                    {{ $item['city']['name'] }}
                </td>
            @endif

            @if ($row['road_span'] > 0)
                <td rowspan="{{ $row['road_span'] }}" data-type="s" style="{{ $cellStyle }}font-weight:bold;">
                    {{ $item['connecting_road'] }}
                </td>
            @endif

            <td data-type="s" style="{{ $cellStyle }}font-weight:bold;">{{ $item['location'] }}</td>
            <td data-type="s" style="{{ $cellStyle }}">{{ $item['customer']['name'] }}</td>
            <td data-type="s" style="{{ $cellStyle }}font-weight:bold;">{{ $item['dimensions'] }}</td>
            <td data-type="n" style="{{ $cellStyle }}font-weight:bold;">{{ $item['total_area'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" data-type="s" style="{{ $cellStyle }}">{{ $labels['empty'] }}</td>
        </tr>
    @endforelse

    <tr>
        <td colspan="6" data-type="s" style="{{ $summaryStyle }}">{{ $labels['totalLabel'] }}</td>
        <td data-type="n" style="{{ $summaryStyle }}">{{ $report['summary']['total_area'] }}</td>
    </tr>
</table>
