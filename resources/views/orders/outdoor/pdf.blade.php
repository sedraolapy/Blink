
@php
    $language = app()->getLocale() === 'en' ? 'en' : 'ar';
    $isArabic = $language === 'ar';

    $items = $order['selected_items'];

    $outdoorType = \App\Enums\ExternalAssetTypeEnum::from(
        $order['outdoor_type']
    )->label();

    $translations = [
        'ar' => [
            'title' => 'أمر تنفيذ أعمال إعلانية خارجية',
            'outdoor_type' => 'نوع الأصل الخارجي',
            'customer' => 'الزبون',
            'period_start' => 'بداية فترة الحجز',
            'period_end' => 'نهاية فترة الحجز',
            'issue_date' => 'تاريخ إصدار الأمر',
            'recipient' => 'الجهة المستلمة',
            'deadline' => 'الموعد النهائي للتنفيذ',
            'number' => 'م',
            'code' => 'كود الأصل',
            'location' => 'موقع الأصل',
            'governorate' => 'المحافظة',
            'dimensions' => 'الأبعاد',
            'design' => 'التصميم',
            'total' => 'إجمالي الأصول',
            'notes' => 'ملاحظات',
            'issuer_signature' => 'توقيع مُصدر الأمر',
            'recipient_signature' => 'توقيع المستلم',
        ],

        'en' => [
            'title' => 'Outdoor Advertising Work Order',
            'outdoor_type' => 'Outdoor Asset Type',
            'customer' => 'Customer',
            'period_start' => 'Booking Start Date',
            'period_end' => 'Booking End Date',
            'issue_date' => 'Issue Date',
            'recipient' => 'Recipient',
            'deadline' => 'Execution Deadline',
            'number' => '#',
            'code' => 'Asset Code',
            'location' => 'Asset Location',
            'governorate' => 'Governorate',
            'dimensions' => 'Dimensions',
            'design' => 'Design',
            'total' => 'Total Assets',
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

    <title>{{ $t['title'] }}</title>

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

        .order-category {
            font-size: 11px;
            color: #667085;
            font-weight: 700;
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
            overflow-wrap: anywhere;
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

        .area {
            color: #667085;
            font-size: 9px;
        }

        .code {
            direction: ltr;
            unicode-bidi: isolate;
            font-size: 10px;
        }

        .dimensions {
            direction: ltr;
            unicode-bidi: isolate;
            white-space: nowrap;
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
            overflow-wrap: anywhere;
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
        <div class="order-category">
            {{ $outdoorType }}
        </div>
    </header>

    <div class="title">
        {{ $t['title'] }}
    </div>

    <section class="info">

        <div class="info-item">
            <span class="info-label">
                {{ $t['customer'] }}
            </span>
            <div class="info-value">
                {{ $order['customer']['name'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">
                {{ $t['outdoor_type'] }}
            </span>
            <div class="info-value">
                {{ $outdoorType }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">
                {{ $t['period_start'] }}
            </span>
            <div class="info-value" dir="ltr">
                {{ $order['period']['start_date'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">
                {{ $t['period_end'] }}
            </span>
            <div class="info-value" dir="ltr">
                {{ $order['period']['end_date'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">
                {{ $t['issue_date'] }}
            </span>
            <div class="info-value" dir="ltr">
                {{ $order['issue_date'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">
                {{ $t['deadline'] }}
            </span>
            <div class="info-value" dir="ltr">
                {{ $order['execution_deadline'] }}
            </div>
        </div>

        <div class="info-item">
            <span class="info-label">
                {{ $t['recipient'] }}
            </span>
            <div class="info-value">
                {{ $order['recipient'] }}
            </div>
        </div>

    </section>

    <table>
        <thead>
            <tr>
                <th style="width: 6%">
                    {{ $t['number'] }}
                </th>
                <th style="width: 17%">
                    {{ $t['code'] }}
                </th>
                <th style="width: 24%">
                    {{ $t['location'] }}
                </th>
                <th style="width: 15%">
                    {{ $t['governorate'] }}
                </th>
                <th style="width: 14%">
                    {{ $t['dimensions'] }}
                </th>
                <th style="width: 24%">
                    {{ $t['design'] }}
                </th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $index => $item)
                @php
                    $asset = $item['asset'];
                @endphp

                <tr>
                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td class="code">
                        {{ $asset['code'] ?? '-' }}
                    </td>

                    <td class="location">
                        {{ $asset['name'] }}

                        @if(!empty($asset['area']))
                            <div class="area">
                                {{ $asset['area'] }}
                            </div>
                        @endif
                    </td>

                    <td>
                        {{ $asset['governorate']['name'] ?? '-' }}
                    </td>

                    <td class="dimensions">
                        @if(
                            $asset['width'] !== null &&
                            $asset['height'] !== null
                        )
                            {{ $asset['width'] }} × {{ $asset['height'] }}
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        {{ $item['design_name'] ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <span>
            {{ $t['total'] }}
        </span>

        <span>
            {{ count($items) }}
        </span>
    </div>

    <section class="notes">
        <div class="notes-title">
            {{ $t['notes'] }}
        </div>

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
