
@php
    $language = app()->getLocale() === 'en' ? 'en' : 'ar';
    $isArabic = $language === 'ar';

    $type = $order['type'];
    $items = $order['selected_items'];

    $translations = [
        'ar' => [
            'installation' => 'أمر تركيب لوحات فليكس',
            'extension' => 'أمر تمديد لوحات فليكس',
            'draft' => 'نموذج تجريبي',
            'period' => 'الفترة الإعلانية',
            'issue_date' => 'تاريخ الإصدار',
            'recipient' => 'الجهة المستلمة',
            'deadline' => 'الموعد النهائي للتنفيذ',
            'number' => 'م',
            'code' => 'كود اللوحة',
            'location' => 'موقع اللوحة',
            'governorate' => 'المحافظة',
            'customer' => 'الزبون',
            'design' => 'التصميم',
            'total' => 'إجمالي اللوحات',
            'notes' => 'ملاحظات',
            'issuer_signature' => 'توقيع مُصدر الأمر',
            'recipient_signature' => 'توقيع المستلم',
        ],
        'en' => [
            'installation' => 'Flex Installation Order',
            'extension' => 'Flex Extension Order',
            'draft' => 'Temporary Template',
            'period' => 'Advertising Period',
            'issue_date' => 'Issue Date',
            'recipient' => 'Recipient',
            'deadline' => 'Execution Deadline',
            'number' => '#',
            'code' => 'Billboard Code',
            'location' => 'Location',
            'governorate' => 'Governorate',
            'customer' => 'Customer',
            'design' => 'Design',
            'total' => 'Total Billboards',
            'notes' => 'Notes',
            'issuer_signature' => 'Issuer Signature',
            'recipient_signature' => 'Recipient Signature',
        ],
    ];

    $t = $translations[$language];

    $fontFile = public_path(
        'quotation/fonts/' .
        ($isArabic
            ? 'cairo-arabic-wght-normal.woff2'
            : 'cairo-latin-wght-normal.woff2')
    );

    $fontData = is_file($fontFile)
        ? 'data:font/woff2;base64,' .
            base64_encode(file_get_contents($fontFile))
        : null;
@endphp

<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        @if($fontData)
        @font-face {
            font-family: 'Cairo PDF';
            src: url("{!! $fontData !!}") format('woff2');
            font-style: normal;
            font-weight: 200 1000;
        }
        @endif

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #263247;
            font-family: 'Cairo PDF', Tahoma, sans-serif;
            font-size: 11px;
            line-height: 1.7;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #17365d;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .brand {
            font-size: 27px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #17365d;
        }

        .draft {
            color: #777;
            font-size: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 4px 10px;
        }

        .title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 22px;
            color: #17365d;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 25px;
        }

        .info-item {
            background: #f4f6f9;
            padding: 10px 12px;
            border-radius: 6px;
        }

        .info-label {
            display: block;
            font-size: 10px;
            color: #667085;
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: 700;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        th {
            background: #17365d;
            color: white;
            font-weight: 700;
            padding: 9px 5px;
            text-align: center;
        }

        td {
            border-bottom: 1px solid #e2e8f0;
            padding: 9px 5px;
            text-align: center;
            overflow-wrap: anywhere;
        }

        tbody tr:nth-child(even) {
            background: #f7f9fc;
        }

        tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .location {
            text-align: start;
        }

        .code {
            direction: ltr;
            unicode-bidi: isolate;
            font-size: 10px;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            background: #edf2f8;
            padding: 12px 16px;
            border-radius: 6px;
            font-weight: 700;
            margin-bottom: 22px;
        }

        .notes {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }

        .notes-title {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .notes-content {
            min-height: 55px;
            padding: 12px;
            border: 1px solid #dce2ea;
            border-radius: 6px;
            white-space: pre-wrap;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature {
            flex: 1;
            text-align: center;
            font-weight: 700;
        }

        .signature-line {
            border-bottom: 1px solid #777;
            margin-top: 45px;
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="brand">BLINK</div>
        <div class="draft">{{ $t['draft'] }}</div>
    </header>

    <div class="title">
        {{ $t[$type] }}
    </div>

    <section class="info">
        <div class="info-item">
            <span class="info-label">{{ $t['period'] }}</span>
            <div class="info-value">
                {{ $order['period']['number'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">{{ $t['issue_date'] }}</span>
            <div class="info-value" dir="ltr">
                {{ $order['issue_date'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">{{ $t['recipient'] }}</span>
            <div class="info-value">
                {{ $order['recipient'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">{{ $t['deadline'] }}</span>
            <div class="info-value" dir="ltr">
                {{ $order['execution_deadline'] }}
            </div>
        </div>
    </section>

    <table>
        <thead>
            <tr>
                <th style="width: 6%">{{ $t['number'] }}</th>
                <th style="width: 19%">{{ $t['code'] }}</th>
                <th style="width: 24%">{{ $t['location'] }}</th>
                <th style="width: 14%">{{ $t['governorate'] }}</th>
                <th style="width: 19%">{{ $t['customer'] }}</th>
                <th style="width: 18%">{{ $t['design'] }}</th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td class="code">
                        {{ $item['flex']['code'] }}
                    </td>

                    <td class="location">
                        {{ $item['flex']['name'] }}
                        @if($item['flex']['area'])
                            <div style="color: #667085; font-size: 9px">
                                {{ $item['flex']['area'] }}
                            </div>
                        @endif
                    </td>

                    <td>
                        {{ $item['flex']['governorate']['name'] ?? '-' }}
                    </td>

                    <td>
                        {{ $item['customer']['name'] }}
                    </td>

                    <td>
                        {{ $item['design_name'] ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <span>{{ $t['total'] }}</span>
        <span>{{ count($items) }}</span>
    </div>

    <section class="notes">
        <div class="notes-title">{{ $t['notes'] }}</div>
        <div class="notes-content">{{ $order['notes'] ?? '' }}</div>
    </section>

    <section class="signatures">
        <div class="signature">
            {{ $t['issuer_signature'] }}
            <div class="signature-line"></div>
        </div>

        <div class="signature">
            {{ $t['recipient_signature'] }}
            <div class="signature-line"></div>
        </div>
    </section>

</body>
</html>
