{{--
Blink — Outdoor Work Order PDF (Laravel Blade)
Styled to match src/features/orders/outdoor/document/OutdoorOrderDocument.jsx
and the shared OrderDocumentHeader/Footer/Signatures components.
Same standalone Chromium PDF approach and embedded assets as the existing Flex Blade.

Expected variable: $order, BUILT FROM THE PERSISTED/AUTHORIZED ISSUANCE, not raw request data.
Example:
$order = [
    'outdoor_type'       => 'mural',        // mural | rooftop | tunnel | bridge | unipole
    'type'               => 'installation', // installation | extension
    'customer'           => ['id' => 9, 'name' => 'شركة مثال'],
    'period'             => ['id' => 210, 'start_date' => '2026-10-01', 'end_date' => '2026-10-29'],
    'issue_date'         => '2026-10-24',
    'recipient'          => 'مدير العمليات الخارجية',
    'execution_deadline' => '2026-10-30',
    'notes'              => '...',         // nullable; blank uses the same default as React
    'items'              => [             // SELECTED, ELIGIBLE ASSETS ONLY (not all candidates)
        [
            'asset' => [
                'id' => 101,
                'code' => 'MUR-LAT-0101',
                'name' => 'جدارية إعلانية اللاذقية - القدسي 101',
                'area' => 'القدسي',
                'width' => 13,
                'height' => 4,
                'governorate' => ['id' => 8, 'name' => 'اللاذقية'],
            ],
            'design_name' => 'Dashboard mural 2026 - 16',
        ],
    ],
];

Backend must enforce selected IDs, permission, booked customer/period/type,
nonempty design_name, and record validity BEFORE rendering this template.
The template never trusts selected_asset_ids or excluded_asset_ids to supply names.

Required existing backend assets (same as Flex PDF):
public/quotation/logos/Arabic.SVG
public/quotation/logos/English.SVG
public/quotation/fonts/cairo-arabic-wght-normal.woff2
public/quotation/fonts/cairo-latin-wght-normal.woff2

Locale follows app()->getLocale() and HTML PDF rendering uses Chromium.
No Vite/Tailwind/JS/external URLs. Page slices use frontend row limits 10/18.
--}}

@php
    $language = app()->getLocale() === 'en' ? 'en' : 'ar';
    $isArabic = $language === 'ar';

    $text = [
        'ar' => [
            'organization' => 'إدارة العمليات',
            'orderLabels' => ['installation' => 'أمر تركيب', 'extension' => 'أمر تمديد'],
            'outdoorLabels' => [
                'mural' => 'الجداريات',
                'rooftop' => 'السطحيات',
                'tunnel' => 'الأنفاق',
                'bridge' => 'الجسور',
                'unipole' => 'يوني بول',
            ],
            'titleTypes' => [
                'mural' => 'جداريات',
                'rooftop' => 'سطحيات',
                'tunnel' => 'أنفاق',
                'bridge' => 'جسور',
                'unipole' => 'يوني بول',
            ],
            'period' => 'الفترة',
            'issueDate' => 'تاريخ الإصدار',
            'to' => 'الجهة المستلمة:',
            'intro' => 'تحية طيبة وبعد، يرجى تنفيذ :operation للوحات :outdoorType المبينة في الكشف أدناه، مع مراعاة البيانات والملاحظات الواردة في هذا الأمر.',
            'deadline' => 'آخر موعد للتنفيذ',
            'customerInfo' => 'بيانات الزبون والأمر',
            'customer' => 'اسم الزبون',
            'outdoorType' => 'نوع الخارجي',
            'operation' => 'نوع الأمر',
            'totalAssets' => 'عدد اللوحات',
            'tableTitle' => 'تفاصيل اللوحات والتصاميم',
            'columns' => [
                'code' => 'الكود',
                'name' => 'اسم اللوحة',
                'dimensions' => 'الأبعاد',
                'governorate' => 'المحافظة',
                'design' => 'التصميم',
            ],
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
            'orderLabels' => ['installation' => 'Installation Order', 'extension' => 'Extension Order'],
            'outdoorLabels' => [
                'mural' => 'Murals',
                'rooftop' => 'Rooftops',
                'tunnel' => 'Tunnels',
                'bridge' => 'Bridges',
                'unipole' => 'Unipoles',
            ],
            'titleTypes' => [
                'mural' => 'Mural',
                'rooftop' => 'Rooftop',
                'tunnel' => 'Tunnel',
                'bridge' => 'Bridge',
                'unipole' => 'Unipole',
            ],
            'period' => 'Period',
            'issueDate' => 'Issue Date',
            'to' => 'Recipient:',
            'intro' => 'Please carry out the :operation for the :outdoorType billboards listed below, in accordance with the details and instructions specified in this order.',
            'deadline' => 'Execution Deadline',
            'customerInfo' => 'Customer and Order Details',
            'customer' => 'Customer Name',
            'outdoorType' => 'Outdoor Type',
            'operation' => 'Order Type',
            'totalAssets' => 'Total Billboards',
            'tableTitle' => 'Billboard and Design Details',
            'columns' => [
                'code' => 'Code',
                'name' => 'Billboard Name',
                'dimensions' => 'Dimensions',
                'governorate' => 'Governorate',
                'design' => 'Design',
            ],
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
    $operationType = $order['type'] ?? null;
    $outdoorType = $order['outdoor_type'] ?? null;
    if (!isset($t['orderLabels'][$operationType])) {
        throw new \InvalidArgumentException('Outdoor PDF: order.type must be installation or extension.');
    }
    if (!isset($t['outdoorLabels'][$outdoorType])) {
        throw new \InvalidArgumentException('Outdoor PDF: order.outdoor_type is invalid.');
    }

    $selectedItems = $order['items'] ?? null;
    if (!is_array($selectedItems) || count($selectedItems) === 0) {
        throw new \InvalidArgumentException('Outdoor PDF: order.items must contain the issued assets.');
    }
    foreach ($selectedItems as $item) {
        if (!isset($item['asset']['id']) || trim((string) ($item['design_name'] ?? '')) === '') {
            throw new \InvalidArgumentException('Outdoor PDF: each selected asset requires an ID and a design.');
        }
    }

    $operationLabel = $t['orderLabels'][$operationType];
    $outdoorLabel = $t['outdoorLabels'][$outdoorType];
    $orderTitle = $isArabic
        ? $operationLabel . ' ' . $t['titleTypes'][$outdoorType]
        : $t['titleTypes'][$outdoorType] . ' ' . $operationLabel;

    $formatDocumentDate = static function ($value): string {
        $value = (string) ($value ?? '');
        $datePart = substr($value, 0, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $datePart);
        return $date && $date->format('Y-m-d') === $datePart
            ? $date->format('d/m/Y')
            : '—';
    };

    $periodStart = $formatDocumentDate($order['period']['start_date'] ?? null);
    $periodEnd = $formatDocumentDate($order['period']['end_date'] ?? null);
    $periodLabel = $periodStart !== '—' && $periodEnd !== '—'
        ? $periodStart . ' – ' . $periodEnd
        : '—';
    $issueDate = $formatDocumentDate($order['issue_date'] ?? null);
    $executionDeadline = $formatDocumentDate($order['execution_deadline'] ?? null);
    $recipient = trim((string) ($order['recipient'] ?? '')) ?: '—';
    $notes = trim((string) ($order['notes'] ?? '')) ?: $t['defaultNotes'];
    $intro = str_replace(
        [':operation', ':outdoorType'],
        [$operationLabel, $outdoorLabel],
        $t['intro'],
    );
    $customerName = $order['customer']['name'] ?? '—';
    $selectedCount = count($selectedItems);

    /* Same frontend ceilings: 10 rows on page 1, 18 on following pages.
       Guard against actual long strings occupying 2+ lines: fixed-row paging
       can clip rows at the footer in both React and Chromium PDF layouts. */
    $firstPageLimit = 10;
    $followingPageLimit = 18;
    $estimateRowHeight = static function ($item) use ($isArabic): int {
        $asset = $item['asset'] ?? [];
        $textLength = static function ($value): int {
            preg_match_all('/\X/u', (string) $value, $matches);
            return count($matches[0] ?? []);
        };
        $nameLines = (int) ceil($textLength($asset['name'] ?? '') / ($isArabic ? 37 : 34));
        $designLines = (int) ceil($textLength($item['design_name'] ?? '') / 28);
        $codeLines = (int) ceil($textLength($asset['code'] ?? '') / 19);
        $lines = max(1, $nameLines, $designLines, $codeLines);
        return 22 + 16 * $lines;
    };

    $tablePages = [[]];
    $tableHeights = [0];
    foreach ($selectedItems as $item) {
        $pageIndex = count($tablePages) - 1;
        $maxRows = $pageIndex === 0 ? $firstPageLimit : $followingPageLimit;
        $heightBudget = $pageIndex === 0 ? 440 : 700;
        $estimatedHeight = $estimateRowHeight($item);
        if (count($tablePages[$pageIndex]) > 0 && (
            count($tablePages[$pageIndex]) >= $maxRows ||
            $tableHeights[$pageIndex] + $estimatedHeight > $heightBudget
        )) {
            $tablePages[] = [];
            $tableHeights[] = 0;
            $pageIndex++;
        }
        $tablePages[$pageIndex][] = $item;
        $tableHeights[$pageIndex] += $estimatedHeight;
    }

    $lastTablePageIndex = count($tablePages) - 1;
    $approvalHeightBudget = count($tablePages) === 1 ? 130 : 370;
    if ($tableHeights[$lastTablePageIndex] > $approvalHeightBudget) {
        $tablePages[] = [];
        $tableHeights[] = 0;
    }

    /* Estimate note wrapping server-side; one physical line is ~24 CSS px.
       React measures actual Cairo line wrapping, so extraordinary text may
       paginate differently. This avoids any JS dependency in the PDF template. */
    $noteLines = [];
    $graphemeLength = static function (string $value): int {
        preg_match_all('/\X/u', $value, $matches);
        return count($matches[0] ?? []);
    };
    $paragraphs = preg_split('/\R/u', $notes) ?: [''];
    foreach ($paragraphs as $paragraph) {
        $words = preg_split('/[ \t]+/u', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!$words) {
            $noteLines[] = '';
            continue;
        }
        $line = '';
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if ($line !== '' && $graphemeLength($candidate) > 86) {
                $noteLines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
            if ($graphemeLength($line) > 86) {
                preg_match_all('/\X/u', $line, $match);
                $pieces = array_chunk($match[0], 86);
                foreach (array_slice($pieces, 0, -1) as $chunk) {
                    $noteLines[] = implode('', $chunk);
                }
                $line = implode('', $pieces[count($pieces) - 1]);
            }
        }
        $noteLines[] = $line;
    }
    if (!$noteLines) {
        $noteLines = [''];
    }

    $pages = [];
    foreach ($tablePages as $tableItems) {
        $pages[] = ['items' => $tableItems, 'notes' => [], 'signatures' => false];
    }
    $lastPageIndex = count($pages) - 1;
    $inlineLimit = count($pages[$lastPageIndex]['items']) === 0 ? 20 : 4;
    if (count($noteLines) <= $inlineLimit) {
        $pages[$lastPageIndex]['notes'] = $noteLines;
        $pages[$lastPageIndex]['signatures'] = true;
    } else {
        if (count($pages[$lastPageIndex]['items']) === 0) {
            array_pop($pages);
        }
        $remaining = $noteLines;
        while (count($remaining) > 20) {
            $take = min(30, count($remaining) - 20);
            $pages[] = ['items' => [], 'notes' => array_splice($remaining, 0, $take), 'signatures' => false];
        }
        $pages[] = ['items' => [], 'notes' => $remaining, 'signatures' => true];
    }
    $pageCount = count($pages);

    /* Same embedded assets as the existing Flex PDF Blade. */
    $assetDataUri = static function (string $path, string $mime): string {
        if (!is_file($path)) {
            throw new \RuntimeException('Outdoor PDF asset not found: ' . $path);
        }
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };
    $assetRoot = public_path('quotation');
    $logo = $assetDataUri(
        $assetRoot . '/logos/' . ($isArabic ? 'Arabic.SVG' : 'English.SVG'),
        'image/svg+xml',
    );
    $fontArabic = $assetDataUri($assetRoot . '/fonts/cairo-arabic-wght-normal.woff2', 'font/woff2');
    $fontLatin = $assetDataUri($assetRoot . '/fonts/cairo-latin-wght-normal.woff2', 'font/woff2');
@endphp

<!doctype html>
<html lang="{{ $language }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $orderTitle }}</title>
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
            white-space: nowrap;
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

        /* Outdoor-specific sections from OutdoorOrderDocument.jsx. */
        .customer-information { margin-top: 24px; }
        .customer-information-title {
            margin-bottom: 12px;
            color: #382d3c;
            font-size: 13px;
            font-weight: 700;
        }
        .customer-information .order-table-shell { border-radius: 8px; }
        .customer-information-table { width: 100%; table-layout: fixed; text-align: center; }
        .customer-information-table th {
            padding: 8px 8px;
            border: 1px solid #ddd5e3;
            background: #f1e8f6;
            color: #4b3a52;
            font-size: 10px;
            font-weight: 700;
        }
        .customer-information-table td {
            padding: 10px 8px;
            border: 1px solid #e1d9e6;
            color: #302733;
            font-size: 11px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
        .customer-information-table col { width: 25%; }
        .distribution { margin-top: 28px; }
        .order-table col:nth-child(1) { width: 17%; }
        .order-table col:nth-child(2) { width: 30%; }
        .order-table col:nth-child(3) { width: 12%; }
        .order-table col:nth-child(4) { width: 15%; }
        .order-table col:nth-child(5) { width: 26%; }
        .order-table th { padding: 12px 6px; }
        .order-table td {
            padding: 10px 8px;
            color: #302733;
            overflow-wrap: anywhere;
            vertical-align: middle;
        }
        .order-table tbody tr:nth-child(odd) td { background: #fcf9fe; }
        .order-table tbody tr:nth-child(even) td { background: #ffffff; }
        .order-table .cell-code { font-weight: 600; }
        .order-table .cell-name { font-weight: 600; text-align: start; }
        .order-table .cell-design { text-align: start; }
        .signatures {
            margin-top: 32px;
        }
        .order-notes {
            margin-top: 32px;
        }
        .order-notes-text {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
        .page-footer { left: 48px; right: 48px; }
        .document-identity {
            border-left-width: 0;
            border-right-width: 0;
        }
        [dir="rtl"] .document-identity {
            border-right: 3px solid #992dff;
            padding-right: 16px;
            padding-left: 0;
        }
        [dir="ltr"] .document-identity {
            border-left: 3px solid #992dff;
            padding-left: 16px;
            padding-right: 0;
        }
        .latin-cell { direction: ltr; unicode-bidi: isolate; }
        .page-footer-copy .latin-dates { direction: ltr; unicode-bidi: isolate; display: inline-block; }
        .page-footer-number { direction: ltr; }

    </style>
</head>
<body>
    @foreach ($pages as $pageIndex => $page)
        @php
            $isFirst = $pageIndex === 0;
            $isLast = $pageIndex === $pageCount - 1;
            $pageItems = $page['items'];
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

            <main class="page-content">
                <div class="document-metadata">
                    <div class="metadata-item">
                        <span class="metadata-label">{{ $t['period'] }}:</span>
                        <strong class="metadata-value latin-cell" dir="ltr">{{ $periodLabel }}</strong>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">{{ $t['issueDate'] }}:</span>
                        <strong class="metadata-value latin-cell" dir="ltr">{{ $issueDate }}</strong>
                    </div>
                </div>

                @if ($isFirst)
                    <div class="document-intro">
                        <h2 class="intro-recipient">{{ $t['to'] }} {{ $recipient }}</h2>
                        <p class="intro-message">{{ $intro }}</p>
                        <p class="intro-deadline">
                            {{ $t['deadline'] }}:
                            <span class="intro-deadline-date latin-cell" dir="ltr">{{ $executionDeadline }}</span>
                        </p>
                    </div>

                    <section class="customer-information">
                        <h2 class="customer-information-title">{{ $t['customerInfo'] }}</h2>
                        <div class="order-table-shell">
                            <table class="customer-information-table">
                                <colgroup><col><col><col><col></colgroup>
                                <thead>
                                    <tr>
                                        <th>{{ $t['customer'] }}</th>
                                        <th>{{ $t['outdoorType'] }}</th>
                                        <th>{{ $t['operation'] }}</th>
                                        <th>{{ $t['totalAssets'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $customerName }}</td>
                                        <td>{{ $outdoorLabel }}</td>
                                        <td>{{ $operationLabel }}</td>
                                        <td>{{ $selectedCount }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if (count($pageItems) > 0)
                    <section class="distribution">
                        <h2 class="distribution-title">{{ $t['tableTitle'] }}</h2>
                        <div class="order-table-shell">
                            <table class="order-table">
                                <colgroup><col><col><col><col><col></colgroup>
                                <thead>
                                    <tr>
                                        <th>{{ $t['columns']['code'] }}</th>
                                        <th>{{ $t['columns']['name'] }}</th>
                                        <th>{{ $t['columns']['dimensions'] }}</th>
                                        <th>{{ $t['columns']['governorate'] }}</th>
                                        <th>{{ $t['columns']['design'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pageItems as $rowIndex => $item)
                                        @php
                                            $asset = $item['asset'];
                                            $dimensions = isset($asset['width'], $asset['height'])
                                                ? $asset['width'] . ' × ' . $asset['height']
                                                : '—';
                                        @endphp
                                        <tr>
                                            <td class="cell-code latin-cell" dir="ltr">{{ $asset['code'] ?? '—' }}</td>
                                            <td class="cell-name">{{ $asset['name'] ?? '—' }}</td>
                                            <td class="latin-cell" dir="ltr">{{ $dimensions }}</td>
                                            <td>{{ $asset['governorate']['name'] ?? '—' }}</td>
                                            <td class="cell-design">{{ $item['design_name'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if (count($page['notes']) > 0)
                    <section class="order-notes">
                        <h2 class="order-notes-heading">{{ $t['notes'] }}</h2>
                        <p class="order-notes-text">{{ $page['notes'] === $noteLines ? $notes : implode("\n", $page['notes']) }}</p>
                    </section>
                @endif

                @if ($page['signatures'])
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
            </main>

            <footer class="page-footer">
                <div class="page-footer-line"></div>
                <div class="page-footer-row">
                    <p class="page-footer-copy">
                        {{ $orderTitle }} · {{ $t['period'] }}
                        <span class="latin-dates" dir="ltr">{{ $periodLabel }}</span>
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
