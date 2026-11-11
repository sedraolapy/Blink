@php
    $isArabic = app()->getLocale() === 'ar';
    $isDamascus = request()->query('region') === 'damascus';

    $labels = $isArabic
        ? [
            'intro' => 'مقدمة : شركة بلينك ميديا للخدمات الإعلامية / 011-9070 / سورية - دمشق',
            'recipient' => 'السادة المؤسسة العربية للإعلان',
            'greeting' => 'تحية وبعد',
            'sentenceStart' => 'نرفق لكم جدول تركيب اللوحات خلال الفترة من تاريخ',
            'sentenceUntil' => 'حتى',
            'governorate' => 'المحافظة',
            'customer' => 'المنتج',
            'count' => 'العدد',
            'dimensions' => 'القياس',
            'area' => 'إجمالي المساحة',
            'booked' => 'إجمالي عدد اللوحات المحجوزة',
            'remaining' => 'إجمالي عدد اللوحات الشاغرة',
            'empty' => 'لا توجد حجوزات مؤهلة للعرض ضمن هذه المجموعة والفترة.',
        ]
        : [
            'intro' => 'Submitted by: Blink Media Services Company / 011-9070 / Syria - Damascus',
            'recipient' => 'To the Arab Advertising Foundation',
            'greeting' => 'Greetings,',
            'sentenceStart' => 'Attached is the billboard installation schedule for the period from',
            'sentenceUntil' => 'to',
            'governorate' => 'Governorate',
            'customer' => 'Client',
            'count' => 'Count',
            'dimensions' => 'Dimensions',
            'area' => 'Total Area',
            'booked' => 'Total Booked Billboards',
            'remaining' => 'Total Available Billboards',
            'empty' => 'No qualifying bookings found for this group and period.',
        ];

    $formatDocDate = static function (string $value): string {
        [$year, $month, $day] = explode('-', $value);

        return $year . ' / ' . (int) $month . ' / ' . (int) $day;
    };

    $textAlign = $isArabic ? 'right' : 'left';
    $font = 'font-family:Calibri,sans-serif;';
    $border = 'border:1px solid #4A4A4A;';
    $headerText = $font . 'font-size:16pt;font-weight:bold;color:#161117;background-color:#FFFFFF;text-align:' . $textAlign . ';vertical-align:middle;word-wrap:break-word;';
    $columnHeader = $font . $border . 'font-size:14pt;font-weight:bold;color:#161117;background-color:#EFEFEF;text-align:center;vertical-align:middle;word-wrap:break-word;';
    $cell = $font . $border . 'font-size:16pt;color:#161117;background-color:#FFFFFF;text-align:center;vertical-align:middle;word-wrap:break-word;';
    $summary = $font . $border . 'font-size:16pt;font-weight:bold;color:#161117;background-color:#BFBFBF;vertical-align:middle;word-wrap:break-word;';
    $blank = $font . $border . 'background-color:#FFFFFF;';

    $bookedText = $bookedLabel !== null && trim($bookedLabel) !== ''
        ? $bookedLabel
        : $labels['booked'];
    $remainingText = $remainingLabel !== null && trim($remainingLabel) !== ''
        ? $remainingLabel
        : $labels['remaining'];
@endphp

<table>
    <tr>
        <td colspan="4" data-type="s" style="{{ $headerText }}">{{ $labels['intro'] }}</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="4" data-type="s" style="{{ $headerText }}">{{ $labels['recipient'] }}</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="4" data-type="s" style="{{ $headerText }}">{{ $labels['greeting'] }}</td>
        <td>&nbsp;</td>
    </tr>
    <tr><td colspan="5">&nbsp;</td></tr>
    <tr><td colspan="5">&nbsp;</td></tr>
    <tr>
        <td colspan="5" data-type="s" style="{{ $headerText }}">
            {{ $labels['sentenceStart'] }} {{ $formatDocDate($from) }} {{ $labels['sentenceUntil'] }} {{ $formatDocDate($to) }}{{ $isArabic ? ' :' : ':' }}
        </td>
    </tr>
    <tr><td colspan="5">&nbsp;</td></tr>

    <tr>
        <th style="{{ $columnHeader }}">{{ $labels['governorate'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['customer'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['count'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['dimensions'] }}</th>
        <th style="{{ $columnHeader }}">{{ $labels['area'] }}</th>
    </tr>

    @if (count($sections) === 0)
        <tr>
            <td colspan="5" data-type="s" style="{{ $cell }}">{{ $labels['empty'] }}</td>
        </tr>
    @endif

    @foreach ($sections as $section)
        @php
            $rowCount = array_sum(array_map(
                static fn (array $entry): int => count($entry['items']),
                $section['customers']
            ));
            $renderedRows = 0;
            $sectionLabel = $isDamascus
                ? $section['label']
                : $section['governorate']['name'];
        @endphp

        @if (!$loop->first)
            <tr><td colspan="5">&nbsp;</td></tr>
        @endif

        @foreach ($section['customers'] as $entry)
            @foreach ($entry['items'] as $item)
                <tr>
                    @if ($renderedRows === 0)
                        <td rowspan="{{ $rowCount }}" data-type="s" style="{{ $cell }}font-weight:bold;">
                            {{ $sectionLabel }}
                        </td>
                    @endif
                    <td data-type="s" style="{{ $cell }}font-weight:bold;text-align:{{ $textAlign }};">
                        {{ $entry['customer']['name'] }}
                    </td>
                    <td data-type="n" style="{{ $cell }}">{{ $item['boards_count'] }}</td>
                    <td data-type="s" style="{{ $cell }}">{{ $item['dimensions'] }}</td>
                    <td data-type="n" style="{{ $cell }}font-weight:bold;">{{ $item['total_area'] }}</td>
                </tr>
                @php $renderedRows++; @endphp
            @endforeach
        @endforeach

        <tr>
            <td colspan="2" data-type="s" style="{{ $summary }}text-align:{{ $textAlign }};">{{ $bookedText }}</td>
            <td colspan="2" data-type="n" style="{{ $summary }}text-align:center;">{{ $section['summary']['booked_boards_count'] }}</td>
            <td data-type="n" style="{{ $summary }}text-align:center;">{{ $section['summary']['total_area'] }}</td>
        </tr>
        <tr>
            <td colspan="2" data-type="s" style="{{ $summary }}text-align:{{ $textAlign }};">{{ $remainingText }}</td>
            <td colspan="2" data-type="n" style="{{ $summary }}text-align:center;">{{ $section['summary']['remaining_boards_count'] }}</td>
            <td style="{{ $blank }}">&nbsp;</td>
        </tr>
    @endforeach
</table>
