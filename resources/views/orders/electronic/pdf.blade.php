
{{--
Blink — Electronic Work Order PDF

Standalone Laravel Blade for the same Chromium/Browsershot
PDF renderer as the existing Flex work order.

Expected variable: $order.
Laravel must build it from persisted issuance records.

$order = [
    'customer' => [
        'id' => 11,
        'name' => 'شركة إعلانات ألفا',
    ],
    'period' => [
        'id' => 40,
        'start_date' => '2026-10-01',
        'end_date' => '2026-11-12',
    ],
    'issue_date' => '2026-10-22',
    'recipient' => 'مدير قسم التشغيل',
    'execution_deadline' => '2026-10-25',
    'notes' => '',
    'screens' => [
        // Selected independent screens only
        [
            'id' => 101,
            'code' => 'LED-101',
            'name' => 'شاشة جسر الرئيس',
            'width' => '5.00',
            'height' => '4.00',
            'resolution' => '1920x1080',
            'governorate' => [
                'id' => 1,
                'name' => 'دمشق',
            ],
            'designs' => ['التصميم الرئيسي'],
        ],
    ],
    'networks' => [
        // Selected networks only, with ALL their screens
        [
            'id' => 1,
            'name' => 'شبكة شاشات دمشق',
            'screens' => [
                // Same screen shape as above
            ],
        ],
    ],
];

Assets reused from the existing Flex PDF:
public/quotation/logos/Arabic.SVG
public/quotation/logos/English.SVG
public/quotation/fonts/cairo-arabic-wght-normal.woff2
public/quotation/fonts/cairo-latin-wght-normal.woff2

No JavaScript or frontend-generated PDF is required.
--}}

@php
    $language = app()->getLocale() === 'en' ? 'en' : 'ar';
    $isArabic = $language === 'ar';

    $text = [
        'ar' => [
            'organization' => 'إدارة العمليات',
            'title' => 'أمر تشغيل الشاشات الإلكترونية',
            'period' => 'الفترة',
            'issueDate' => 'تاريخ الإصدار',
            'to' => 'الجهة المستلمة:',
            'intro' => 'تحية طيبة وبعد، يرجى تنفيذ أعمال تشغيل الشاشات والشبكات الإلكترونية المبينة في الكشف أدناه، مع مراعاة البيانات والملاحظات الواردة في هذا الأمر.',
            'deadline' => 'آخر موعد للتنفيذ',

            'customerInfo' => 'بيانات الزبون والفترة',
            'customer' => 'اسم الزبون',
            'startDate' => 'من تاريخ',
            'endDate' => 'إلى تاريخ',
            'totalScreens' => 'عدد الشاشات',

            'tableTitle' => 'تفاصيل الشاشات والتصاميم',
            'independentGroup' => 'الشاشات المستقلة',
            'networkGroup' => 'الشبكة: :name · عدد الشاشات: :count',

            'columns' => [
                'code' => 'الكود',
                'name' => 'اسم الشاشة',
                'dimensions' => 'الأبعاد',
                'resolution' => 'الدقة',
                'governorate' => 'المحافظة',
                'designs' => 'التصاميم',
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
            'title' => 'Electronic Screens Operation Order',
            'period' => 'Period',
            'issueDate' => 'Issue Date',
            'to' => 'Recipient:',
            'intro' => 'Please carry out the operation of the electronic screens and networks listed below, in accordance with the details and instructions specified in this order.',
            'deadline' => 'Execution Deadline',

            'customerInfo' => 'Customer and Period Details',
            'customer' => 'Customer Name',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'totalScreens' => 'Total Screens',

            'tableTitle' => 'Screen and Design Details',
            'independentGroup' => 'Independent Screens',
            'networkGroup' => 'Network: :name · Screens: :count',

            'columns' => [
                'code' => 'Code',
                'name' => 'Screen Name',
                'dimensions' => 'Dimensions',
                'resolution' => 'Resolution',
                'governorate' => 'Governorate',
                'designs' => 'Designs',
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

    $customer = $order['customer'] ?? [];
    $period = $order['period'] ?? [];
    $screens = $order['screens'] ?? [];
    $networks = $order['networks'] ?? [];

    if (
        !is_array($customer) ||
        !is_array($period) ||
        !is_array($screens) ||
        !is_array($networks)
    ) {
        throw new \InvalidArgumentException(
            'Electronic PDF: invalid order data.'
        );
    }

    /*
     * Document dates
     */
    $formatDocumentDate = static function ($value): string {
        $part = substr((string) ($value ?? ''), 0, 10);

        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $part
        );

        return $date && $date->format('Y-m-d') === $part
            ? $date->format('d/m/Y')
            : '—';
    };

    $startDate = $formatDocumentDate(
        $period['start_date'] ?? null
    );

    $endDate = $formatDocumentDate(
        $period['end_date'] ?? null
    );

    $periodLabel =
        $startDate !== '—' && $endDate !== '—'
            ? $startDate . ' – ' . $endDate
            : '—';

    $issueDate = $formatDocumentDate(
        $order['issue_date'] ?? null
    );

    $executionDeadline = $formatDocumentDate(
        $order['execution_deadline'] ?? null
    );

    $recipient = trim(
        (string) ($order['recipient'] ?? '')
    ) ?: '—';

    $notes = trim(
        (string) ($order['notes'] ?? '')
    ) ?: $t['defaultNotes'];

    /*
     * Build document groups.
     *
     * Independent screens are shown first.
     * Every selected network is shown as one group.
     */
    $groups = [];

    $totalScreensCount = count($screens);

    if (count($screens) > 0) {
        $groups[] = [
            'kind' => 'independent',
            'id' => 'independent',
            'screens' => array_values($screens),
        ];
    }

    foreach ($networks as $network) {
        $networkScreens = $network['screens'] ?? [];

        if (
            !is_array($networkScreens) ||
            count($networkScreens) === 0
        ) {
            throw new \InvalidArgumentException(
                'Electronic PDF: selected network has no screens.'
            );
        }

        $totalScreensCount += count($networkScreens);

        $groups[] = [
            'kind' => 'network',
            'id' => $network['id'] ?? null,
            'name' => $network['name'] ?? '—',
            'screens' => array_values($networkScreens),
        ];
    }

    if ($totalScreensCount === 0) {
        throw new \InvalidArgumentException(
            'Electronic PDF: no selected screens or networks.'
        );
    }

    /*
     * Table pagination.
     *
     * Matches the current React electronic document
     * row-count pagination model.
     */
    $firstPageRows = 8;
    $followingPageRows = 12;

    $firstPageWithApprovalsRows = 4;
    $followingPageWithApprovalsRows = 6;

    $pages = [[]];

    foreach ($groups as $group) {
        $headingOnPage = false;

        foreach ($group['screens'] as $screen) {
            $index = count($pages) - 1;

            $limit = count($pages) === 1
                ? $firstPageRows
                : $followingPageRows;

            $required = $headingOnPage ? 1 : 2;

            if (
                count($pages[$index]) > 0 &&
                count($pages[$index]) + $required > $limit
            ) {
                $pages[] = [];

                $index = count($pages) - 1;
                $headingOnPage = false;
            }

            if (!$headingOnPage) {
                $pages[$index][] = [
                    'kind' => 'group',
                    'group' => $group,
                ];

                $headingOnPage = true;
            }

            $pages[$index][] = [
                'kind' => 'screen',
                'screen' => $screen,
            ];
        }
    }

    $lastCount = count(
        $pages[count($pages) - 1]
    );

    $approvalCapacity = count($pages) === 1
        ? $firstPageWithApprovalsRows
        : $followingPageWithApprovalsRows;

    if ($lastCount > $approvalCapacity) {
        $pages[] = [];
    }

    /*
     * Notes pagination.
     *
     * Same approach as Flex Blade:
     * estimate wrapped lines without JavaScript.
     */
    $notesNormalized = str_replace(
        ["\r\n", "\r"],
        "\n",
        $notes
    );

    $estimateWidth = static function (string $part): float {
        $width = 0.0;

        foreach (
            preg_split('//u', $part, -1, PREG_SPLIT_NO_EMPTY)
            as $char
        ) {
            if (preg_match('/^\s$/u', $char)) {
                $width += 4.0;
            } elseif (preg_match('/^\p{Arabic}$/u', $char)) {
                $width += 8.5;
            } elseif (preg_match('/^[A-Z]$/u', $char)) {
                $width += 10.5;
            } elseif (preg_match('/^[a-z0-9]$/u', $char)) {
                $width += 7.5;
            } else {
                $width += 10.0;
            }
        }

        return $width;
    };

    $estimatedLines = [];
    $maxEstimatedWidth = 570;

    $sourceLines = explode(
        "\n",
        $notesNormalized
    );

    foreach ($sourceLines as $sourceIndex => $sourceLine) {
        preg_match_all(
            '/\S+|\s+/u',
            $sourceLine,
            $matched
        );

        $currentText = '';
        $currentWidth = 0.0;

        foreach ($matched[0] as $token) {
            $tokenWidth = $estimateWidth($token);
            $isWord = trim($token) !== '';

            if (
                $isWord &&
                $currentWidth > 0 &&
                $currentWidth + $tokenWidth > $maxEstimatedWidth
            ) {
                $estimatedLines[] = $currentText;
                $currentText = '';
                $currentWidth = 0.0;
            }

            if ($tokenWidth > $maxEstimatedWidth) {
                foreach (
                    preg_split(
                        '//u',
                        $token,
                        -1,
                        PREG_SPLIT_NO_EMPTY
                    ) as $char
                ) {
                    $charWidth = $estimateWidth($char);

                    if (
                        $currentWidth > 0 &&
                        $currentWidth + $charWidth > $maxEstimatedWidth
                    ) {
                        $estimatedLines[] = $currentText;
                        $currentText = '';
                        $currentWidth = 0.0;
                    }

                    $currentText .= $char;
                    $currentWidth += $charWidth;
                }
            } else {
                $currentText .= $token;
                $currentWidth += $tokenWidth;
            }
        }

        $estimatedLines[] =
            $currentText .
            (
                $sourceIndex < count($sourceLines) - 1
                    ? "\n"
                    : ''
            );
    }

    /*
     * Allocate final pages for notes and signatures.
     */
    $lastRowsCount = count(
        $pages[count($pages) - 1]
    );

    $inlineLimit = $lastRowsCount === 0 ? 20 : 4;

    $lineCount = count($estimatedLines);
    $documentPages = [];

    if ($lineCount <= $inlineLimit) {
        foreach ($pages as $index => $entries) {
            $isLast = $index === count($pages) - 1;

            $documentPages[] = [
                'entries' => $entries,
                'notes' => $isLast ? $notesNormalized : null,
                'signatures' => $isLast,
            ];
        }
    } else {
        if ($lastRowsCount === 0) {
            array_pop($pages);
        }

        foreach ($pages as $entries) {
            $documentPages[] = [
                'entries' => $entries,
                'notes' => null,
                'signatures' => false,
            ];
        }

        $normalPageLimit = 30;
        $signaturesPageLimit = 20;

        $notesPageCount =
            $lineCount <= $signaturesPageLimit
                ? 1
                : (int) ceil(
                    ($lineCount - $signaturesPageLimit) /
                    $normalPageLimit
                ) + 1;

        $remaining = $lineCount;

        for (
            $index = 0;
            $index < $notesPageCount;
            ++$index
        ) {
            $pagesAfter = $notesPageCount - $index - 1;

            $futureCapacity = $pagesAfter > 0
                ? ($pagesAfter - 1) * $normalPageLimit +
                    $signaturesPageLimit
                : 0;

            $maxHere = $pagesAfter === 0
                ? $signaturesPageLimit
                : $normalPageLimit;

            $take = min(
                $maxHere,
                max(
                    (int) ceil(
                        $remaining / ($pagesAfter + 1)
                    ),
                    $remaining - $futureCapacity
                )
            );

            $chunk = array_splice(
                $estimatedLines,
                0,
                $take
            );

            $documentPages[] = [
                'entries' => [],
                'notes' => implode('', $chunk),
                'signatures' => $pagesAfter === 0,
            ];

            $remaining -= $take;
        }
    }

    /*
     * Embedded assets — same as Flex.
     */
    $assetDataUri = static function (
        string $path,
        string $mime
    ): string {
        if (!is_file($path)) {
            throw new \RuntimeException(
                'Electronic PDF asset not found: ' . $path
            );
        }

        return 'data:' . $mime . ';base64,' .
            base64_encode(file_get_contents($path));
    };

    $assetRoot = public_path('quotation');

    $logo = $assetDataUri(
        $assetRoot . '/logos/' .
            ($isArabic ? 'Arabic.SVG' : 'English.SVG'),
        'image/svg+xml'
    );

    $fontArabic = $assetDataUri(
        $assetRoot . '/fonts/cairo-arabic-wght-normal.woff2',
        'font/woff2'
    );

    $fontLatin = $assetDataUri(
        $assetRoot . '/fonts/cairo-latin-wght-normal.woff2',
        'font/woff2'
    );

    $pageCount = count($documentPages);
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

html[lang="ar"],
html[lang="ar"] body {
    font-family: "Cairo Arabic", "Cairo Latin", sans-serif;
}

html[lang="en"],
html[lang="en"] body {
    font-family: "Cairo Latin", "Cairo Arabic", sans-serif;
}

body {
    line-height: inherit;
    -webkit-font-smoothing: antialiased;
}

h1, h2, h3, p {
    margin: 0;
    font-size: inherit;
    font-weight: inherit;
}

table {
    border-collapse: collapse;
    border-color: inherit;
    text-indent: 0;
}

img {
    display: block;
    max-width: 100%;
}

.pdf-page,
.pdf-page * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.pdf-page {
    position: relative;
    width: 210mm;
    height: 297mm;
    min-height: 297mm;
    overflow: hidden;
    background: #ffffff;
    color: #302733;
    break-after: page;
    page-break-after: always;
}

.pdf-page--last {
    break-after: auto;
    page-break-after: auto;
}

/* Shared document header */

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

/* Order metadata */

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

/* First-page introduction */

.document-intro {
    margin-top: 32px;
}

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

.intro-deadline-date {
    display: inline-block;
}

/* Customer and period details */

.customer-section {
    margin-top: 24px;
}

.customer-section-title {
    margin-bottom: 12px;
    color: #382d3c;
    font-size: 13px;
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

.order-table th {
    background: #f1e8f6;
    border: 1px solid #ddd5e3;
    color: #4b3a52;
    font-size: 10px;
    font-weight: 700;
}

.customer-table th {
    padding: 8px;
}

.customer-table td {
    border: 1px solid #e1d9e6;
    padding: 10px 8px;
    font-size: 10px;
    font-weight: 400;
    overflow-wrap: break-word;
}

.customer-table td.customer-name {
    font-size: 11px;
    font-weight: 600;
}

.customer-table td.customer-count {
    font-size: 11px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

/* Screens and networks table */

.screens-section {
    margin-top: 28px;
}

.screens-section-title {
    margin-bottom: 12px;
    color: #382d3c;
    font-size: 14px;
    font-weight: 700;
}

.screens-table col:nth-child(1) {
    width: 17%;
}

.screens-table col:nth-child(2) {
    width: 25%;
}

.screens-table col:nth-child(3) {
    width: 11%;
}

.screens-table col:nth-child(4) {
    width: 15%;
}

.screens-table col:nth-child(5) {
    width: 13%;
}

.screens-table col:nth-child(6) {
    width: 19%;
}

.screens-table th {
    padding: 12px 6px;
    line-height: 16px;
}

.screens-table td {
    padding: 10px 8px;
    border: 1px solid #e1d9e6;
    color: #302733;
    font-size: 10px;
    font-weight: 500;
    line-height: 16px;
    overflow-wrap: break-word;
    vertical-align: middle;
}

.screens-table .screen-tint > td {
    background: #fcf9fe;
}

.screens-table .screen-white > td {
    background: #ffffff;
}

.screens-table .group-row td {
    padding: 8px 12px;
    border: 1px solid #ddd5e3;
    background: #f7f1fa;
    color: #4b3a52;
    font-size: 11px;
    font-weight: 700;
    text-align: center;
}

.screens-table .code-cell,
.screens-table .name-cell {
    font-weight: 600;
}

.screens-table .name-cell,
.screens-table .designs-cell {
    text-align: start;
}

.screens-table .design-line + .design-line {
    margin-top: 4px;
}

/* Notes */

.order-notes {
    margin-top: 32px;
}

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

/* Signatures — same as Flex */

.signatures {
    margin-top: 32px;
}

.sales-signatures {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 40px;
}

.signature-block {
    text-align: center;
}

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

/* Footer — same as Flex */

.page-footer {
    position: absolute;
    left: 48px;
    right: 48px;
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
@foreach ($documentPages as $pageIndex => $page)
    @php
        $entries = $page['entries'];
        $noteChunk = $page['notes'];
        $showSignatures = $page['signatures'];

        $isFirst = $pageIndex === 0;
        $isLast = $pageIndex === $pageCount - 1;
    @endphp

    <section class="pdf-page{{ $isLast ? ' pdf-page--last' : '' }}">

        <!-- Shared header -->
        <header class="document-header">
            <div class="document-header-row">

                <div class="document-identity">
                    <p class="document-organization">
                        {{ $t['organization'] }}
                    </p>

                    <h1 class="document-title">
                        {{ $t['title'] }}
                    </h1>
                </div>

                <img
                    class="document-logo"
                    src="{{ $logo }}"
                    alt="Blink"
                >
            </div>
        </header>

        <div class="page-content">

            <!-- Shared metadata -->
            <div class="document-metadata">

                <div class="metadata-item">
                    <span class="metadata-label">
                        {{ $t['period'] }}:
                    </span>

                    <strong
                        class="metadata-value"
                        dir="ltr"
                    >
                        {{ $periodLabel }}
                    </strong>
                </div>

                <div class="metadata-item">
                    <span class="metadata-label">
                        {{ $t['issueDate'] }}:
                    </span>

                    <strong
                        class="metadata-value"
                        dir="ltr"
                    >
                        {{ $issueDate }}
                    </strong>
                </div>
            </div>

            <!-- Introduction — first page only -->
            @if ($isFirst)
                <div class="document-intro">

                    <h2 class="intro-recipient">
                        {{ $t['to'] }} {{ $recipient }}
                    </h2>

                    <p class="intro-message">
                        {{ $t['intro'] }}
                    </p>

                    <p class="intro-deadline">
                        {{ $t['deadline'] }}:

                        <span
                            class="intro-deadline-date"
                            dir="ltr"
                        >
                            {{ $executionDeadline }}
                        </span>
                    </p>
                </div>

                <!-- Customer and period -->
                <section class="customer-section">

                    <h2 class="customer-section-title">
                        {{ $t['customerInfo'] }}
                    </h2>

                    <div class="order-table-shell">

                        <table class="order-table customer-table">

                            <thead>
                                <tr>
                                    <th>{{ $t['customer'] }}</th>
                                    <th>{{ $t['startDate'] }}</th>
                                    <th>{{ $t['endDate'] }}</th>
                                    <th>{{ $t['totalScreens'] }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="customer-name">
                                        {{ $customer['name'] ?? '—' }}
                                    </td>

                                    <td dir="ltr">
                                        {{ $startDate }}
                                    </td>

                                    <td dir="ltr">
                                        {{ $endDate }}
                                    </td>

                                    <td class="customer-count">
                                        {{ $totalScreensCount }}
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </section>
            @endif

            <!-- Screens and networks -->
            @if (count($entries) > 0)
                <section class="screens-section">

                    <h2 class="screens-section-title">
                        {{ $t['tableTitle'] }}
                    </h2>

                    <div class="order-table-shell">

                        <table class="order-table screens-table">

                            <colgroup>
                                <col><col><col>
                                <col><col><col>
                            </colgroup>

                            <thead>
                                <tr>
                                    @foreach (
                                        [
                                            'code',
                                            'name',
                                            'dimensions',
                                            'resolution',
                                            'governorate',
                                            'designs'
                                        ] as $column
                                    )
                                        <th>
                                            {{ $t['columns'][$column] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($entries as $index => $entry)

                                    @if ($entry['kind'] === 'group')
                                        <tr class="group-row">
                                            <td colspan="6">

                                                @if (
                                                    $entry['group']['kind']
                                                    === 'network'
                                                )
                                                    {{
                                                        str_replace(
                                                            [':name', ':count'],
                                                            [
                                                                $entry['group']['name'],
                                                                (string) count(
                                                                    $entry['group']['screens']
                                                                )
                                                            ],
                                                            $t['networkGroup']
                                                        )
                                                    }}
                                                @else
                                                    {{ $t['independentGroup'] }}
                                                @endif

                                            </td>
                                        </tr>

                                    @else
                                        @php
                                            $screen = $entry['screen'];

                                            $dimensions = isset(
                                                $screen['width'],
                                                $screen['height']
                                            )
                                                ? $screen['width'] . ' × ' .
                                                    $screen['height']
                                                : '—';

                                            $designs = is_array(
                                                $screen['designs'] ?? null
                                            )
                                                ? $screen['designs']
                                                : [];
                                        @endphp

                                        <tr class="{{ $index % 2 ? 'screen-white' : 'screen-tint' }}">

                                            <td
                                                class="code-cell"
                                                dir="ltr"
                                            >
                                                {{ ($screen['code'] ?? null) ?: '—' }}
                                            </td>

                                            <td class="name-cell">
                                                {{ ($screen['name'] ?? null) ?: '—' }}
                                            </td>

                                            <td dir="ltr">
                                                {{ $dimensions }}
                                            </td>

                                            <td dir="ltr">
                                                {{ ($screen['resolution'] ?? null) ?: '—' }}
                                            </td>

                                            <td>
                                                {{
                                                    $screen['governorate']['name']
                                                    ?? '—'
                                                }}
                                            </td>

                                            <td class="designs-cell">

                                                @forelse ($designs as $design)
                                                    <p
                                                        class="design-line"
                                                        dir="{{ $isArabic ? 'auto' : 'ltr' }}"
                                                    >
                                                        {{ $design }}
                                                    </p>
                                                @empty
                                                    —
                                                @endforelse

                                            </td>
                                        </tr>
                                    @endif

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <!-- Notes -->
            @if ($noteChunk !== null)
                <section class="order-notes">

                    <h2 class="order-notes-heading">
                        {{ $t['notes'] }}
                    </h2>

<p class="order-notes-text">{{ $noteChunk }}</p>
                </section>
            @endif

            <!-- Shared signatures -->
            @if ($showSignatures)
                <section class="signatures">

                    <div class="sales-signatures">

                        @foreach (
                            ['coordinator', 'manager']
                            as $signatureType
                        )
                            <div class="signature-block">

                                <h3 class="signature-heading">
                                    {{
                                        $t['signatures'][$signatureType]
                                    }}
                                </h3>

                                <div class="signature-line"></div>

                                <p class="signature-caption">
                                    {{ $t['nameAndSignature'] }}
                                </p>

                            </div>
                        @endforeach

                    </div>

                    <div class="signature-block finance-signature">

                        <h3 class="signature-heading">
                            {{ $t['signatures']['finance'] }}
                        </h3>

                        <div class="signature-line"></div>

                        <p class="signature-caption">
                            {{ $t['nameAndSignature'] }}
                        </p>

                    </div>

                </section>
            @endif

        </div>

        <!-- Shared footer -->
        <footer class="page-footer">

            <div class="page-footer-line"></div>

            <div class="page-footer-row">

                <p class="page-footer-copy">
                    {{ $t['title'] }}
                    ·
                    {{ $t['period'] }}

                    <span dir="ltr">
                        {{ $periodLabel }}
                    </span>
                </p>

                <div class="page-footer-accent"></div>

                <p
                    class="page-footer-number"
                    dir="ltr"
                >
                    {{
                        str_replace(
                            ':page',
                            (string) ($pageIndex + 1),
                            $t['pageNumber']
                        )
                    }}
                </p>

            </div>
        </footer>

    </section>
@endforeach
</body>
</html>
