@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = $isArabic
        ? [
            'intro' => 'مقدمة : شركة بلينك للخدمات الإعلامية - 9070-011 / دمشق - سورية',
            'recipient' => 'السادة المؤسسة العربية للإعلان',
            'greeting' => 'تحية وبعد',
            'sentenceStart' => 'نرفق لكم جدول الشاشات الإلكترونية خلال الفترة من تاريخ',
            'sentenceUntil' => 'حتى',
            'location' => 'الموقع',
            'width' => 'العرض',
            'height' => 'الارتفاع',
            'area' => 'المساحة',
            'city' => 'المدينة',
            'present' => 'يوجد',
            'cityTotal' => 'إجمالي إلكتروني',
            'empty' => 'لا توجد بيانات ضمن هذا الشهر.',
        ]
        : [
            'intro' => 'Introduction: Blink Media Services Company - 9070-011 / Damascus - Syria',
            'recipient' => 'To the Arab Advertising Foundation',
            'greeting' => 'Greetings,',
            'sentenceStart' => 'Attached is the electronic screens schedule for the period from',
            'sentenceUntil' => 'until',
            'location' => 'Location',
            'width' => 'Width',
            'height' => 'Height',
            'area' => 'Area',
            'city' => 'City',
            'present' => 'Present',
            'cityTotal' => 'Total Electronic Screens in',
            'empty' => 'No data found for this month.',
        ];

    $formatDate = static function (string $date): string {
        [$year, $month, $day] = explode('-', $date);

        return $year . ' / ' . (int) $month . ' / ' . (int) $day;
    };


$font = 'font-family:Calibri,sans-serif;';
$border = 'border:1px solid #4A4A4A;';

$documentText = $font . 'background-color:#FFFFFF;color:#161117;font-size:16pt;font-weight:bold;vertical-align:middle;text-align:' . ($isArabic ? 'right' : 'left') . ';';

$columnHeader = $font . $border . 'background-color:#EFEFEF;color:#161117;font-size:14pt;font-weight:bold;text-align:center;vertical-align:middle;';

$cellStyle = $font . $border . 'background-color:#FFFFFF;color:#161117;font-size:16pt;text-align:center;vertical-align:middle;';

$totalStyle = $font . $border . 'background-color:#BFBFBF;color:#161117;font-size:16pt;font-weight:bold;text-align:center;vertical-align:middle;';

$rowNumber = 0;
@endphp

{{-- One HTML table is required for predictable FromView cell positions.
     Header content occupies A:E; F:G is reserved for the Excel drawing.
     The seven-column report body does not merge any cells. --}}
<table>
    <tr>
        <td colspan="5" data-type="s" style="{{ $documentText }}">{{ $labels['intro'] }}</td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="5" data-type="s" style="{{ $documentText }}">{{ $labels['recipient'] }}</td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="5" data-type="s" style="{{ $documentText }}">{{ $labels['greeting'] }}</td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr><td colspan="7">&nbsp;</td></tr>
    <tr>
        <td colspan="5" data-type="s" style="{{ $documentText }}">
            {{ $labels['sentenceStart'] }} {{ $formatDate($from) }} {{ $labels['sentenceUntil'] }} {{ $formatDate($to) }}
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr><td colspan="7">&nbsp;</td></tr>

    <tr>
        <th style="{{ $columnHeader }}">#</th>
        <th style="{{ $columnHeader }}">{{ $labels['location'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['width'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['height'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['area'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['city'] }}</th>
        <th style="{{ $columnHeader }}">&nbsp;</th>
    </tr>

    @if (count($sections) === 0)
        <tr>
            <td style="{{ $cellStyle }}">&nbsp;</td>
            <td data-type="s" style="{{ $cellStyle }}">{{ $labels['empty'] }}</td>
            <td style="{{ $cellStyle }}">&nbsp;</td>
            <td style="{{ $cellStyle }}">&nbsp;</td>
            <td style="{{ $cellStyle }}">&nbsp;</td>
            <td style="{{ $cellStyle }}">&nbsp;</td>
            <td style="{{ $cellStyle }}">&nbsp;</td>
        </tr>
    @endif

    @foreach ($sections as $section)
        @foreach ($section['items'] as $item)
            <tr>
                <td data-type="n" style="{{ $cellStyle }}">{{ ++$rowNumber }}</td>
                <td data-type="s" style="{{ $cellStyle }}font-weight:bold;">{{ $item['location'] }}</td>
                <td data-type="s" style="{{ $cellStyle }}">{{ $item['width_m'] }}m ({{ $item['width_px'] }} px)</td>
                <td data-type="s" style="{{ $cellStyle }}">{{ $item['height_m'] }}m ({{ $item['height_px'] }} px)</td>
                <td data-type="n" style="{{ $cellStyle }}font-weight:bold;">{{ $item['area'] }}</td>
                <td data-type="s" style="{{ $cellStyle }}">{{ $section['city']['name'] }}</td>
                <td data-type="s" style="{{ $cellStyle }}">{{ $labels['present'] }}</td>
            </tr>
        @endforeach

        <tr>
            <td style="{{ $totalStyle }}">&nbsp;</td>
            <td data-type="s" style="{{ $totalStyle }}">{{ $labels['cityTotal'] }} {{ $section['city']['name'] }}</td>
            <td style="{{ $totalStyle }}">&nbsp;</td>
            <td style="{{ $totalStyle }}">&nbsp;</td>
            <td data-type="n" style="{{ $totalStyle }}">{{ $section['summary']['total_area'] }}</td>
            <td style="{{ $totalStyle }}">&nbsp;</td>
            <td style="{{ $totalStyle }}">&nbsp;</td>
        </tr>
    @endforeach
</table>
