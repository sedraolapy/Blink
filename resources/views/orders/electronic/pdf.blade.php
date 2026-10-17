
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <title>أمر تشغيل إلكتروني</title>

    <style>
        @page {
            size: A4;
            margin: 14mm 11mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.8;
            color: #222;
            direction: rtl;
        }

        .header {
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 2px solid #243b53;
        }

        .header h1 {
            margin: 0;
            font-size: 23px;
            color: #243b53;
        }

        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 12px;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .details td {
            padding: 9px 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .details .label {
            display: block;
            color: #666;
            font-size: 10px;
        }

        .details .value {
            display: block;
            font-weight: bold;
            font-size: 12px;
        }

        .section-title {
            font-size: 15px;
            color: #243b53;
            border-right: 4px solid #243b53;
            padding-right: 10px;
            margin: 22px 0 12px;
            break-after: avoid;
        }

        .network-heading {
            background: #edf2f7;
            border: 1px solid #d5dce5;
            padding: 9px 12px;
            font-weight: bold;
            margin-top: 16px;
            break-after: avoid;
        }

        .network-heading span {
            font-size: 10px;
            color: #666;
            font-weight: normal;
            margin-right: 10px;
        }

        .screens-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 16px;
        }

        .screens-table th {
            background: #edf2f7;
            color: #243b53;
            font-size: 10px;
            padding: 8px 5px;
            border: 1px solid #d5dce5;
            text-align: right;
        }

        .screens-table td {
            padding: 8px 5px;
            border: 1px solid #ddd;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .screens-table tr {
            break-inside: avoid;
        }

        .screens-table thead {
            display: table-header-group;
        }

        .code {
            direction: ltr;
            text-align: left;
            font-size: 10px;
        }

        .design {
            padding: 2px 0;
            border-bottom: 1px dashed #e5e5e5;
        }

        .design:last-child {
            border-bottom: none;
        }

        .notes {
            margin-top: 24px;
            border: 1px solid #ddd;
            padding: 12px;
            break-inside: avoid;
        }

        .notes strong {
            color: #243b53;
        }

        .notes-content {
            margin-top: 5px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 10px;
        }

        .number {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>أمر تشغيل شاشات إلكترونية</h1>
        <p>تفاصيل الشاشات والشبكات المطلوب تشغيلها</p>
    </div>

    <table class="details">
        <tr>
            <td>
                <span class="label">اسم العميل</span>
                <span class="value">
                    {{ $order['customer']['name'] }}
                </span>
            </td>

            <td>
                <span class="label">تاريخ إصدار الأمر</span>
                <span class="value">
                    {{ $order['issue_date'] }}
                </span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">تاريخ بداية الحجز</span>
                <span class="value">
                    {{ $order['period']['start_date'] }}
                </span>
            </td>

            <td>
                <span class="label">تاريخ نهاية الحجز</span>
                <span class="value">
                    {{ $order['period']['end_date'] }}
                </span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="label">الجهة المستلمة</span>
                <span class="value">
                    {{ $order['recipient'] }}
                </span>
            </td>

            <td>
                <span class="label">آخر موعد للتنفيذ</span>
                <span class="value">
                    {{ $order['execution_deadline'] }}
                </span>
            </td>
        </tr>
    </table>

    @if(count($order['screens']) > 0)

        <h2 class="section-title">
            أولاً: الشاشات الإلكترونية المستقلة
        </h2>

        <table class="screens-table">
            <thead>
                <tr>
                    <th style="width: 14%">الكود</th>
                    <th style="width: 20%">الشاشة</th>
                    <th style="width: 17%">الموقع</th>
                    <th style="width: 12%">الأبعاد</th>
                    <th style="width: 13%">الدقة</th>
                    <th style="width: 24%">التصاميم</th>
                </tr>
            </thead>

            <tbody>
                @foreach($order['screens'] as $screen)
                    <tr>
                        <td class="code">
                            {{ $screen['code'] ?? '—' }}
                        </td>

                        <td>
                            {{ $screen['name'] }}
                        </td>

                        <td>
                            {{ $screen['governorate']['name'] ?? '—' }}
                            -
                            {{ $screen['area'] ?? '—' }}
                        </td>

                        <td>
                            {{ $screen['width'] }}
                            ×
                            {{ $screen['height'] }}
                        </td>

                        <td class="code">
                            {{ $screen['resolution'] ?? '—' }}
                        </td>

                        <td>
                            @forelse($screen['designs'] as $design)
                                <div class="design">
                                    {{ $design }}
                                </div>
                            @empty
                                —
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

    @if(count($order['networks']) > 0)

        <h2 class="section-title">
            الشبكات الإلكترونية
        </h2>

        @foreach($order['networks'] as $network)

            <div class="network-heading">
                {{ $network['name'] }}

                <span>
                    عدد الشاشات:
                    <span class="number">
                        {{ count($network['screens']) }}
                    </span>
                </span>
            </div>

            <table class="screens-table">
                <thead>
                    <tr>
                        <th style="width: 14%">الكود</th>
                        <th style="width: 20%">الشاشة</th>
                        <th style="width: 17%">الموقع</th>
                        <th style="width: 12%">الأبعاد</th>
                        <th style="width: 13%">الدقة</th>
                        <th style="width: 24%">التصاميم</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($network['screens'] as $screen)
                        <tr>
                            <td class="code">
                                {{ $screen['code'] ?? '—' }}
                            </td>

                            <td>
                                {{ $screen['name'] }}
                            </td>

                            <td>
                                {{ $screen['governorate']['name'] ?? '—' }}
                                -
                                {{ $screen['area'] ?? '—' }}
                            </td>

                            <td>
                                {{ $screen['width'] }}
                                ×
                                {{ $screen['height'] }}
                            </td>

                            <td class="code">
                                {{ $screen['resolution'] ?? '—' }}
                            </td>

                            <td>
                                @forelse($screen['designs'] as $design)
                                    <div class="design">
                                        {{ $design }}
                                    </div>
                                @empty
                                    —
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @endforeach

    @endif

    @if(!empty($order['notes']))
        <div class="notes">
            <strong>ملاحظات أمر التشغيل:</strong>

            <div class="notes-content">{{ $order['notes'] }}</div>
        </div>
    @endif

    <div class="footer">
        تم إعداد هذا الأمر بناءً على بيانات الحجز الإلكتروني المحدد.
    </div>

</body>
</html>
