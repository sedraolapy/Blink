{{--
Blink — Flex Work Order PDF
Standalone Laravel Blade view with inline CSS, following the quotation PDF template.

Expected view variable: $order (associative array built from the PERSISTED issuance).
Example shape:
$order = [
    'type' => 'installation',                     // installation | extension
    'period' => ['id' => 20, 'number' => 20],
    'issue_date' => '2026-10-21',                 // YYYY-MM-DD
    'recipient' => 'السيد مدير المعمل',
    'execution_deadline' => '2026-10-25',         // YYYY-MM-DD
    'notes' => '...',                             // optional; an empty value uses the localized default
    'items' => [                                 // SELECTED boards ONLY; one element per board
        [
            'flex' => [
                'id' => 101,
                'governorate' => ['id' => 1, 'name' => 'دمشق'],
            ],
            'customer' => ['id' => 15, 'name' => 'شركة مثال'],
        ],
    ],
];

The backend must fetch customer, governorate, period and selected board records
from its own database, NOT from request-supplied names or quantities.
The order type already determines the list of candidates server-side.

Locale follows app()->getLocale(), as in the supplied quotation PDF template.
Reuses the same backend assets; no Vite, Tailwind, external URLs or JavaScript:
public/quotation/logos/Arabic.SVG
public/quotation/logos/English.SVG
public/quotation/fonts/cairo-arabic-wght-normal.woff2
public/quotation/fonts/cairo-latin-wght-normal.woff2

Intended for a Chromium-based HTML-to-PDF renderer, just like the quotation template.
--}}

@php
    $language = app()->getLocale() === 'en' ? 'en' : 'ar';
    $isArabic = $language === 'ar';

    $text = [
        'ar' => [
            'organization' => 'إدارة العمليات',
            'titles' => [
                'installation' => 'أمر تركيب',
                'extension' => 'أمر تمديد',
            ],
            'work' => [
                'installation' => 'التركيب',
                'extension' => 'التمديد',
            ],
            'period' => 'الفترة',
            'issueDate' => 'تاريخ الإصدار',
            'to' => 'الجهة المستلمة:',
            'intro' => 'تحية طيبة وبعد، يرجى تنفيذ أعمال :work للوحات الفليكس المبينة في الكشف أدناه، مع مراعاة البيانات والملاحظات الواردة في الأمر.',
            'deadline' => 'آخر موعد للتنفيذ',
            'tableTitle' => 'كشف توزيع اللوحات',
            'columns' => [
                'number' => 'م',
                'customer' => 'اسم الشركة',
                'governorate' => 'المحافظة',
                'boardsCount' => 'عدد اللوحات',
                'financialDepartment' => 'القسم المالي',
            ],
            'total' => 'الإجمالي',
            'notes' => 'ملاحظات',
            'defaultNotes' => 'يرجى التنسيق مع منسق المبيعات قبل بدء التنفيذ، والالتزام بالمواعيد والملاحظات المذكورة في هذا الأمر.',
            'signatures' => [
                'coordinator' => 'منسق المبيعات',
                'manager' => 'مدير المبيعات',
                'finance' => 'المدير المالي',
            ],
            'nameAndSignature' => 'الاسم والتوقيع',
            'pageNumber' => 'الصفحة :page',
        ],
        'en' => [
            'organization' => 'Operations Department',
            'titles' => [
                'installation' => 'Installation Order',
                'extension' => 'Extension Order',
            ],
            'work' => [
                'installation' => 'installation',
                'extension' => 'extension',
            ],
            'period' => 'Period',
            'issueDate' => 'Issue Date',
            'to' => 'Recipient:',
            'intro' => 'Please carry out the :work of the Flex billboards listed in the schedule below, in accordance with the information and notes stated in this order.',
            'deadline' => 'Execution Deadline',
            'tableTitle' => 'Billboard Distribution Schedule',
            'columns' => [
                'number' => 'No.',
                'customer' => 'Company',
                'governorate' => 'Governorate',
                'boardsCount' => 'Billboards',
                'financialDepartment' => 'Finance',
            ],
            'total' => 'Total',
            'notes' => 'Notes',
            'defaultNotes' => 'Please coordinate with the Sales Coordinator before commencing work and comply with the deadlines and instructions stated in this order.',
            'signatures' => [
                'coordinator' => 'Sales Coordinator',
                'manager' => 'Sales Manager',
                'finance' => 'Finance Manager',
            ],
            'nameAndSignature' => 'Name and signature',
            'pageNumber' => 'Page :page',
        ],
    ];
    $t = $text[$language];

    $order = $order ?? [];
    $orderType = $order['type'] ?? null;
    if (!isset($t['titles'][$orderType])) {
        throw new \InvalidArgumentException('Flex PDF: order.type must be installation or extension.');
    }

    $selectedItems = $order['items'] ?? [];
    if (!is_array($selectedItems) || count($selectedItems) === 0) {
        throw new \InvalidArgumentException('Flex PDF: order.items must contain the issued Flex boards.');
    }

    $orderTitle = $t['titles'][$orderType];
    $workLabel = $t['work'][$orderType];

    $periodNumber = $order['period']['number'] ?? null;
    $periodLabel = $periodNumber === null
        ? '—'
        : str_pad((string) $periodNumber, 2, '0', STR_PAD_LEFT);

    $formatDocumentDate = static function ($value): string {
        $value = (string) ($value ?? '');
        $datePart = substr($value, 0, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $datePart);

        return $date && $date->format('Y-m-d') === $datePart
            ? $date->format('d/m/Y')
            : '—';
    };

    $issueDate = $formatDocumentDate($order['issue_date'] ?? null);
    $executionDeadline = $formatDocumentDate($order['execution_deadline'] ?? null);
    $recipient = trim((string) ($order['recipient'] ?? '')) ?: '—';
    $notes = trim((string) ($order['notes'] ?? '')) ?: $t['defaultNotes'];
    $intro = str_replace(':work', $workLabel, $t['intro']);
    $selectedCount = count($selectedItems);

    /* Exact PHP equivalent of buildFlexOrderDocumentData() in React.
       The service supplies only selected, authorized, persisted items. */
    $customers = [];
    foreach ($selectedItems as $itemIndex => $item) {
        $flex = $item['flex'] ?? [];
        $customer = $item['customer'] ?? [];
        $governorate = $flex['governorate'] ?? [];

        $customerKey = isset($customer['id'])
            ? 'id:' . $customer['id']
            : 'name:' . ($customer['name'] ?? ($flex['id'] ?? $itemIndex));
        $governorateKey = isset($governorate['id'])
            ? 'id:' . $governorate['id']
            : 'name:' . ($governorate['name'] ?? ($flex['id'] ?? $itemIndex));

        if (!isset($customers[$customerKey])) {
            $customers[$customerKey] = [
                'key' => $customerKey,
                'name' => $customer['name'] ?? '—',
                'governorates' => [],
            ];
        }

        if (!isset($customers[$customerKey]['governorates'][$governorateKey])) {
            $customers[$customerKey]['governorates'][$governorateKey] = [
                'key' => $governorateKey,
                'name' => $governorate['name'] ?? '—',
                'count' => 0,
            ];
        }

        ++$customers[$customerKey]['governorates'][$governorateKey]['count'];
    }

    $rows = [];
    $serial = 0;
    foreach ($customers as $customer) {
        ++$serial;
        foreach ($customer['governorates'] as $governorate) {
            $rows[] = [
                'customerKey' => $customer['key'],
                'customerName' => $customer['name'],
                'serial' => $serial,
                'governorateKey' => $governorate['key'],
                'governorateName' => $governorate['name'],
                'boardsCount' => $governorate['count'],
            ];
        }
    }

    /* Exact current React pagination limits and logic.
       Last table page contains Total, even when signatures occupy an empty page. */
    $firstPageRows = 12;
    $followingPageRows = 16;
    $firstPageWithApprovalsRows = 5;
    $followingPageWithApprovalsRows = 8;

    if (count($rows) <= $firstPageWithApprovalsRows) {
        $pages = [$rows];
    } else {
        $pages = [array_slice($rows, 0, $firstPageRows)];
        for ($offset = $firstPageRows; $offset < count($rows); $offset += $followingPageRows) {
            $pages[] = array_slice($rows, $offset, $followingPageRows);
        }
        $lastPage = $pages[count($pages) - 1];
        $approvalCapacity = count($pages) === 1
            ? $firstPageWithApprovalsRows
            : $followingPageWithApprovalsRows;
        if (count($lastPage) > $approvalCapacity) {
            $pages[] = [];
        }
    }

    $pageCount = count($pages);

    /* The same embedded assets used by the quotation Blade PDF. */
    $assetDataUri = static function (string $path, string $mime): string {
        if (!is_file($path)) {
            throw new \RuntimeException('Flex PDF asset not found: ' . $path);
        }
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    $assetRoot = public_path('quotation');
    $logo = $assetDataUri(
        $assetRoot . '/logos/' . ($isArabic ? 'Arabic.SVG' : 'English.SVG'),
        'image/svg+xml',
    );
    $fontArabic = $assetDataUri(
        $assetRoot . '/fonts/cairo-arabic-wght-normal.woff2',
        'font/woff2',
    );
    $fontLatin = $assetDataUri(
        $assetRoot . '/fonts/cairo-latin-wght-normal.woff2',
        'font/woff2',
    );
@endphp

<!doctype html>
<html lang="{{ $language }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        @font-face {
            font-family: "Cairo Arabic";
            src: url("{!! $fontArabic !!}") format("woff2");
            font-style: normal;
            font-weight: 200 1000;
            font-display: block;
        }

        @font-face {
            font-family: "Cairo Latin";
            src: url("{!! $fontLatin !!}") format("woff2");
            font-style: normal;
            font-weight: 200 1000;
            font-display: block;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: currentColor;
        }

        html {
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 210mm;
            background: #ffffff;
            color: #302733;
        }

        html[lang="ar"], html[lang="ar"] body {
            font-family: "Cairo Arabic", "Cairo Latin", sans-serif;
        }

        html[lang="en"], html[lang="en"] body {
            font-family: "Cairo Latin", "Cairo Arabic", sans-serif;
        }

        body {
            line-height: inherit;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, p { margin: 0; font-size: inherit; font-weight: inherit; }
        table { border-collapse: collapse; border-color: inherit; text-indent: 0; }
        img { display: block; max-width: 100%; }

        .pdf-page, .pdf-page * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .pdf-page {
            position: relative;
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            overflow: hidden;
            color: #302733;
            background: #ffffff;
            break-after: page;
            page-break-after: always;
        }

        .pdf-page--last {
            break-after: auto;
            page-break-after: auto;
        }

        /* Header: mirrors FlexOrderDocument.jsx (without preview shadow). */
        .document-header {
            margin: 0 48px;
            padding: 32px 0 16px;
            border-bottom: 1px solid #e8e2ec;
        }

        .document-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
        }

        .document-identity {
            min-width: 0;
            padding-inline-start: 16px;
            border-inline-start: 3px solid #992dff;
        }

        .document-organization {
            color: #928798;
            font-size: 10px;
            font-weight: 500;
        }

        .document-title {
            margin-top: 4px;
            color: #322737;
            font-size: 22px;
            font-weight: 700;
            line-height: 32px;
        }

        .document-logo {
            flex: 0 0 auto;
            width: 120px;
            height: auto;
            object-fit: contain;
        }

        .page-content {
            padding: 24px 48px 96px;
        }

        /* Compact issue information strip. */
        .document-metadata {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 10px 16px;
            background: #faf8fb;
        }

        .metadata-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .metadata-label {
            color: #817486;
            font-size: 10px;
            font-weight: 500;
        }

        .metadata-value {
            color: #302733;
            font-size: 11px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* Only the first page shows the recipient, introduction and deadline. */
        .document-intro { margin-top: 32px; }

        .intro-recipient {
            color: #332836;
            font-size: 16px;
            font-weight: 700;
            line-height: 28px;
        }

        .intro-message {
            margin-top: 8px;
            color: #675d6a;
            font-size: 13px;
            font-weight: 500;
            line-height: 32px;
            overflow-wrap: break-word;
        }

        .intro-deadline {
            margin-top: 16px;
            color: #403544;
            font-size: 14px;
            font-weight: 600;
        }

        .intro-deadline-date { display: inline-block; }

        /* The same five-column table as the React preview. */
        .distribution { margin-top: 32px; }

        .distribution-title {
            margin-bottom: 12px;
            color: #382d3c;
            font-size: 14px;
            font-weight: 700;
        }

        .order-table-shell {
            overflow: hidden;
            border: 1px solid #ddd5e3;
            border-radius: 8px;
        }

        .order-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            text-align: center;
        }

        .order-table col:nth-child(1) { width: 8%; }
        .order-table col:nth-child(2) { width: 34%; }
        .order-table col:nth-child(3) { width: 22%; }
        .order-table col:nth-child(4) { width: 14%; }
        .order-table col:nth-child(5) { width: 22%; }

        .order-table th {
            padding: 12px 10px;
            border: 1px solid #ddd5e3;
            background: #f1e8f6;
            color: #4b3a52;
            font-size: 10px;
            font-weight: 700;
            line-height: 16px;
        }

        .order-table td {
            padding: 12px 10px;
            border: 1px solid #e1d9e6;
            color: #000000;
            font-size: 10px;
            font-weight: 500;
            line-height: 16px;
            overflow-wrap: break-word;
        }

        /* Stripe by COMPANY, not by governorate row: works with rowspan. */
        .order-table .company-white > td { background: #ffffff; }
        .order-table .company-tint > td { background: #fcf9fe; }
        .order-table .cell-semibold { font-weight: 600; }
        .order-table .cell-bold { font-weight: 700; }
        .order-table .cell-start { text-align: start; }
        .order-table .total-row > td { background: #f7f1fa; }

        /* Last page only: notes and three signature spaces. */
        .order-notes { margin-top: 32px; }
        .order-notes-heading {
            color: #382d3c;
            font-size: 13px;
            font-weight: 700;
        }

        .order-notes-text {
            margin-top: 8px;
            color: #675d6a;
            font-size: 12px;
            font-weight: 500;
            line-height: 24px;
            white-space: pre-wrap;
            overflow-wrap: break-word;
        }

        .signatures { margin-top: 32px; }
        .sales-signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 40px;
        }

        .signature-block { text-align: center; }
        .signature-heading {
            color: #403544;
            font-size: 12px;
            font-weight: 700;
        }

        .signature-line {
            width: 85%;
            height: 24px;
            margin: 20px auto 0;
            border-bottom: 1px solid #c9c1ce;
        }

        .signature-caption {
            margin-top: 8px;
            color: #9b919f;
            font-size: 10px;
        }

        .finance-signature {
            width: 45%;
            margin: 32px auto 0;
        }

        /* Footer: same visual grammar as QuotationDocumentPage.jsx. */
        .page-footer {
            position: absolute;
            inset-inline: 48px;
            bottom: 32px;
        }

        .page-footer-line {
            height: 1px;
            margin-bottom: 12px;
            background: #e8e2ec;
        }

        .page-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .page-footer-copy {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #8f8594;
            font-size: 9.5px;
            font-weight: 500;
        }

        .page-footer-accent {
            flex: 0 0 auto;
            width: 48px;
            height: 3px;
            border-radius: 9999px;
            background: #8801fe;
        }

        .page-footer-number {
            flex: 0 0 auto;
            color: #8f8594;
            font-size: 9.5px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @foreach ($pages as $pageIndex => $pageRows)
        @php
            $isFirst = $pageIndex === 0;
            $isLast = $pageIndex === $pageCount - 1;
            $isLastTablePage = count($pageRows) > 0
                && ($isLast || count($pages[$pageIndex + 1] ?? []) === 0);

            /* Rebuild rowSpan groups on each page, exactly as OrderTable() does. */
            $pageGroups = [];
            foreach ($pageRows as $row) {
                $lastGroupIndex = count($pageGroups) - 1;
                if ($lastGroupIndex >= 0
                    && $pageGroups[$lastGroupIndex]['key'] === $row['customerKey']) {
                    $pageGroups[$lastGroupIndex]['rows'][] = $row;
                } else {
                    $pageGroups[] = [
                        'key' => $row['customerKey'],
                        'serial' => $row['serial'],
                        'name' => $row['customerName'],
                        'rows' => [$row],
                    ];
                }
            }
        @endphp

        <section class="pdf-page{{ $isLast ? ' pdf-page--last' : '' }}">
            <header class="document-header">
                <div class="document-header-row">
                    <div class="document-identity">
                        <p class="document-organization">{{ $t['organization'] }}</p>
                        <h1 class="document-title">{{ $orderTitle }}</h1>
                    </div>
                    <img class="document-logo" src="{{ $logo }}" alt="Blink">
                </div>
            </header>

            <div class="page-content">
                <div class="document-metadata">
                    <div class="metadata-item">
                        <span class="metadata-label">{{ $t['period'] }}:</span>
                        <strong class="metadata-value" dir="ltr">{{ $periodLabel }}</strong>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">{{ $t['issueDate'] }}:</span>
                        <strong class="metadata-value" dir="ltr">{{ $issueDate }}</strong>
                    </div>
                </div>

                @if ($isFirst)
                    <div class="document-intro">
                        <h2 class="intro-recipient">{{ $t['to'] }} {{ $recipient }}</h2>
                        <p class="intro-message">{{ $intro }}</p>
                        <p class="intro-deadline">
                            {{ $t['deadline'] }}:
                            <span class="intro-deadline-date" dir="ltr">{{ $executionDeadline }}</span>
                        </p>
                    </div>
                @endif

                @if (count($pageRows) > 0)
                    <section class="distribution">
                        <h2 class="distribution-title">{{ $t['tableTitle'] }}</h2>
                        <div class="order-table-shell">
                            <table class="order-table">
                                <colgroup><col><col><col><col><col></colgroup>
                                <thead>
                                    <tr>
                                        <th>{{ $t['columns']['number'] }}</th>
                                        <th>{{ $t['columns']['customer'] }}</th>
                                        <th>{{ $t['columns']['governorate'] }}</th>
                                        <th>{{ $t['columns']['boardsCount'] }}</th>
                                        <th>{{ $t['columns']['financialDepartment'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pageGroups as $group)
                                        @foreach ($group['rows'] as $rowIndex => $row)
                                            <tr class="{{ $group['serial'] % 2 === 1 ? 'company-white' : 'company-tint' }}">
                                                @if ($rowIndex === 0)
                                                    <td rowspan="{{ count($group['rows']) }}" class="cell-semibold">
                                                        {{ $group['serial'] }}
                                                    </td>
                                                    <td rowspan="{{ count($group['rows']) }}" class="cell-semibold">
                                                        {{ $group['name'] }}
                                                    </td>
                                                @endif

                                                <td>{{ $row['governorateName'] }}</td>
                                                <td class="cell-bold">{{ $row['boardsCount'] }}</td>

                                                @if ($rowIndex === 0)
                                                    <td rowspan="{{ count($group['rows']) }}"></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach

                                    @if ($isLastTablePage)
                                        <tr class="total-row">
                                            <td colspan="3" class="cell-start cell-bold">{{ $t['total'] }}</td>
                                            <td class="cell-bold">{{ $selectedCount }}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($isLast)
                    <section class="order-notes">
                        <h2 class="order-notes-heading">{{ $t['notes'] }}</h2>
                        <p class="order-notes-text">{{ $notes }}</p>
                    </section>

                    <section class="signatures">
                        <div class="sales-signatures">
                            @foreach (['coordinator', 'manager'] as $signatureType)
                                <div class="signature-block">
                                    <h3 class="signature-heading">{{ $t['signatures'][$signatureType] }}</h3>
                                    <div class="signature-line"></div>
                                    <p class="signature-caption">{{ $t['nameAndSignature'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="signature-block finance-signature">
                            <h3 class="signature-heading">{{ $t['signatures']['finance'] }}</h3>
                            <div class="signature-line"></div>
                            <p class="signature-caption">{{ $t['nameAndSignature'] }}</p>
                        </div>
                    </section>
                @endif
            </div>

            <footer class="page-footer">
                <div class="page-footer-line"></div>
                <div class="page-footer-row">
                    <p class="page-footer-copy">
                        {{ $orderTitle }} · {{ $t['period'] }} {{ $periodLabel }}
                    </p>
                    <div class="page-footer-accent"></div>
                    <p class="page-footer-number" dir="ltr">
                        {{ str_replace(':page', (string) ($pageIndex + 1), $t['pageNumber']) }}
                    </p>
                </div>
            </footer>
        </section>
    @endforeach
</body>
</html>
