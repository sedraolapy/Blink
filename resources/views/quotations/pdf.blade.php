{{--
Blink Quotation PDF Template
Standalone Blade-ready template with inline CSS.

Expected variables:
- $quotation    : same data structure returned in data from GET /bookings/{booking_id}/quotation
- $language     : "ar" or "en"
- $documentDate : formatted as yyyy/MM/dd
- $finalAmount  : nullable; final payable amount after discount for the current issue

Expected backend assets:
public/quotation/logos/Arabic.SVG
public/quotation/logos/Arabic-white.SVG
public/quotation/logos/English.SVG
public/quotation/logos/English-white.SVG
public/quotation/logos/logo.svg
public/quotation/fonts/cairo-arabic-wght-normal.woff2
public/quotation/fonts/cairo-latin-wght-normal.woff2
--}}

@php
    $quotation = $quotation ?? [];
    $language = ($language ?? 'ar') === 'en' ? 'en' : 'ar';
    $isArabic = $language === 'ar';
    $documentDate = $documentDate ?? '';
    $finalAmount = $finalAmount ?? null;

    $text = [
        'ar' => [
            'title' => 'عرض سعر',
            'preparedFor' => 'مقدم إلى',
            'advertiserType' => 'نوع المعلن',
            'issueDate' => 'تاريخ الإصدار',
            'pageNumber' => 'الصفحة :page',
            'advertiser' => [
                'local' => 'محلي',
                'foreign' => 'أجنبي',
            ],
            'intro' => [
                'recipient' => 'السادة المحترمون: :customer',
                'message' => 'تحية طيبة وبعد، يسرنا أن نقدم لكم عرض سعر للمساحات الإعلانية المحجوزة وفق التفاصيل التالية:',
                'offerType' => 'نوع العرض: :type',
            ],
            'flex' => [
                'summaryTitle' => 'اللوحات الطرقية فليكس - توزيع اللوحات حسب المحافظة',
                'detailsTitle' => 'تفاصيل لوحات الفليكس',
                'governorateDetails' => 'المواقع التفصيلية - :governorate',
                'total' => 'إجمالي قيمة حجز لوحات الفليكس',
                'columns' => [
                    'governorate' => 'المحافظة',
                    'boardsCount' => 'عدد اللوحات',
                    'networksCount' => 'عدد الشبكات',
                    'periodNumber' => 'رقم الفترة',
                    'networkPrice' => 'سعر الشبكة',
                    'totalPrice' => 'السعر الإجمالي',
                    'id' => 'ID اللوحة',
                    'location' => 'الموقع',
                    'size' => 'القياس',
                ],
            ],
            'electronic' => [
                'summaryTitle' => 'الشاشات الإلكترونية - توزيع الشاشات حسب المحافظة',
                'standaloneTitle' => 'تفاصيل الشاشات الإلكترونية المفردة',
                'governorateDetails' => 'المواقع التفصيلية - :governorate',
                'networksTitle' => 'تفاصيل الشبكات الإلكترونية',
                'networkTitle' => ':network - :governorate',
                'networkTotal' => 'إجمالي قيمة الشبكة',
                'total' => 'إجمالي قيمة حجز الشاشات الإلكترونية',
                'columns' => [
                    'governorate' => 'المحافظة',
                    'screensCount' => 'عدد الشاشات',
                    'networksCount' => 'عدد الشبكات',
                    'period' => 'الفترة',
                    'totalPrice' => 'السعر الإجمالي',
                    'id' => 'ID الشاشة',
                    'location' => 'الموقع',
                    'size' => 'القياس',
                    'resolution' => 'الدقة',
                    'slidesCount' => 'عدد السلايدات',
                    'price' => 'السعر',
                ],
            ],
            'outdoor' => [
                'summaryTitle' => ':type - توزيع اللوحات حسب المحافظة',
                'detailsTitle' => 'تفاصيل :type',
                'governorateDetails' => 'المواقع التفصيلية - :governorate',
                'total' => 'إجمالي قيمة حجز :type',
                'columns' => [
                    'governorate' => 'المحافظة',
                    'assetsCount' => 'عدد اللوحات',
                    'period' => 'الفترة',
                    'totalPrice' => 'السعر الإجمالي',
                    'id' => 'ID اللوحة',
                    'location' => 'الموقع',
                    'size' => 'القياس',
                    'price' => 'السعر',
                ],
                'types' => [
                    'mural' => 'جداريات',
                    'rooftop' => 'سطحيات',
                    'tunnel' => 'أنفاق',
                    'bridge' => 'جسور',
                    'unipole' => 'يوني بول',
                ],
            ],
            'finalSummary' => [
                'bookingType' => 'نوع الحجز',
                'amount' => 'القيمة',
                'title' => 'ملخص قيمة الحجز',
                'flex' => 'إجمالي لوحات الفليكس',
                'electronic' => 'إجمالي الشاشات الإلكترونية',
                'outdoor' => 'إجمالي :type',
                'grandTotal' => 'إجمالي قيمة الحجز',
                'finalAmount' => 'القيمة النهائية بعد الحسم',
                'customerSignature' => 'توقيع العميل',
            ],
        ],
        'en' => [
            'title' => 'Quotation',
            'preparedFor' => 'Prepared for',
            'advertiserType' => 'Advertiser Type',
            'issueDate' => 'Issue Date',
            'pageNumber' => 'Page :page',
            'advertiser' => [
                'local' => 'Local',
                'foreign' => 'Foreign',
            ],
            'intro' => [
                'recipient' => 'Dear :customer,',
                'message' => 'Greetings, we are pleased to present our quotation for the reserved advertising spaces according to the following details:',
                'offerType' => 'Offer Type: :type',
            ],
            'flex' => [
                'summaryTitle' => 'Flex Roadside Billboards - Distribution by Governorate',
                'detailsTitle' => 'Flex Billboard Details',
                'governorateDetails' => 'Detailed Locations - :governorate',
                'total' => 'Total Flex Billboard Booking Value',
                'columns' => [
                    'governorate' => 'Governorate',
                    'boardsCount' => 'Boards',
                    'networksCount' => 'Networks',
                    'periodNumber' => 'Period',
                    'networkPrice' => 'Network Price',
                    'totalPrice' => 'Total Price',
                    'id' => 'Board ID',
                    'location' => 'Location',
                    'size' => 'Size',
                ],
            ],
            'electronic' => [
                'summaryTitle' => 'Electronic Screens - Distribution by Governorate',
                'standaloneTitle' => 'Standalone Electronic Screen Details',
                'governorateDetails' => 'Detailed Locations - :governorate',
                'networksTitle' => 'Electronic Network Details',
                'networkTitle' => ':network - :governorate',
                'networkTotal' => 'Network Total',
                'total' => 'Total Electronic Screens Booking Value',
                'columns' => [
                    'governorate' => 'Governorate',
                    'screensCount' => 'Screens',
                    'networksCount' => 'Networks',
                    'period' => 'Period',
                    'totalPrice' => 'Total Price',
                    'id' => 'Screen ID',
                    'location' => 'Location',
                    'size' => 'Size',
                    'resolution' => 'Resolution',
                    'slidesCount' => 'Slides',
                    'price' => 'Price',
                ],
            ],
            'outdoor' => [
                'summaryTitle' => ':type - Distribution by Governorate',
                'detailsTitle' => ':type Details',
                'governorateDetails' => 'Detailed Locations - :governorate',
                'total' => 'Total :type Booking Value',
                'columns' => [
                    'governorate' => 'Governorate',
                    'assetsCount' => 'Assets',
                    'period' => 'Period',
                    'totalPrice' => 'Total Price',
                    'id' => 'Asset ID',
                    'location' => 'Location',
                    'size' => 'Size',
                    'price' => 'Price',
                ],
                'types' => [
                    'mural' => 'Murals',
                    'rooftop' => 'Rooftops',
                    'tunnel' => 'Tunnels',
                    'bridge' => 'Bridges',
                    'unipole' => 'Unipoles',
                ],
            ],
            'finalSummary' => [
                'bookingType' => 'Booking Type',
                'amount' => 'Amount',
                'title' => 'Booking Value Summary',
                'flex' => 'Flex Billboards Total',
                'electronic' => 'Electronic Screens Total',
                'outdoor' => ':type Total',
                'grandTotal' => 'Total Booking Value',
                'finalAmount' => 'Final Amount After Discount',
                'customerSignature' => 'Customer Signature',
            ],
        ],
    ];

    $t = $text[$language];

    $replace = static function (string $value, array $variables = []): string {
        foreach ($variables as $key => $replacement) {
            $value = str_replace(':' . $key, (string) $replacement, $value);
        }

        return $value;
    };

    $formatUsd = static function ($value): string {
        $number = number_format((float) $value, 2, '.', ',');
        $number = rtrim(rtrim($number, '0'), '.');

        return 'USD ' . $number;
    };

    $formatDimension = static function ($value): string {
        $number = number_format((float) $value, 10, '.', '');
        $number = rtrim(rtrim($number, '0'), '.');

        return $number === '' ? '0' : $number;
    };

    $formatSize = static function ($width, $height) use ($formatDimension): string {
        return $formatDimension($width) . ' × ' . $formatDimension($height) . ' m';
    };

    $formatDate = static function ($value): string {
        return str_replace('-', '/', (string) $value);
    };

    $formatDateRange = static function (array $period) use ($formatDate, $isArabic): string {
        $startDate = $formatDate($period['start_date'] ?? '');
        $endDate = $formatDate($period['end_date'] ?? '');

        return $isArabic
            ? $endDate . ' - ' . $startDate
            : $startDate . ' - ' . $endDate;
    };

    /*
     * Exact PHP translation of the frontend buildGroupedDetailPages helper.
     * This is presentation pagination only; no pricing/business calculation happens here.
     */
    $buildGroupedDetailPages = static function (array $groups, int $rowsPerPage): array {
        $pages = [];
        $currentPage = [];
        $usedRows = 0;

        foreach ($groups as $group) {
            $items = $group['items'] ?? [];
            $itemIndex = 0;
            $itemsCount = count($items);

            while ($itemIndex < $itemsCount) {
                $groupTitleSpace = 1;
                $availableRows = $rowsPerPage - $usedRows - $groupTitleSpace;

                if ($availableRows <= 0) {
                    if (count($currentPage) > 0) {
                        $pages[] = $currentPage;
                    }

                    $currentPage = [];
                    $usedRows = 0;

                    continue;
                }

                $startIndex = $itemIndex;
                $chunkItems = array_slice($items, $startIndex, $availableRows);

                $pageGroup = $group;
                $pageGroup['items'] = $chunkItems;
                $pageGroup['isContinuation'] = $startIndex > 0;
                $pageGroup['isLastChunk'] =
                    $startIndex + count($chunkItems) >= $itemsCount;

                $currentPage[] = $pageGroup;

                $usedRows += $groupTitleSpace + count($chunkItems);
                $itemIndex += count($chunkItems);

                if ($usedRows >= $rowsPerPage) {
                    $pages[] = $currentPage;
                    $currentPage = [];
                    $usedRows = 0;
                }
            }
        }

        if (count($currentPage) > 0) {
            $pages[] = $currentPage;
        }

        return $pages;
    };

    /*
     * Embed local backend assets as data URIs so Chromium does not depend on
     * Vite, a frontend URL, the internet, or local-file access flags.
     */
    $assetDataUri = static function (string $path, string $mime): string {
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    $quotationAssetRoot = public_path('quotation');

    $logoColored = $assetDataUri(
        $quotationAssetRoot . '/logos/' . ($isArabic ? 'Arabic.SVG' : 'English.SVG'),
        'image/svg+xml',
    );

    $logoWhite = $assetDataUri(
        $quotationAssetRoot . '/logos/' . ($isArabic ? 'Arabic-white.SVG' : 'English-white.SVG'),
        'image/svg+xml',
    );

    $brandSymbol = $assetDataUri(
        $quotationAssetRoot . '/logos/logo.svg',
        'image/svg+xml',
    );

    $fontArabic = $assetDataUri(
        $quotationAssetRoot . '/fonts/cairo-arabic-wght-normal.woff2',
        'font/woff2',
    );

    $fontLatin = $assetDataUri(
        $quotationAssetRoot . '/fonts/cairo-latin-wght-normal.woff2',
        'font/woff2',
    );

    /*
     * Quotation data — display only.
     */
    $customerName = $quotation['customer']['name'];
    $advertiserType = $quotation['advertiser_type'];
    $advertiserTypeLabel = $t['advertiser'][$advertiserType] ?? $advertiserType;

    $flex = $quotation['flex'] ?? [
        'summary' => [],
        'details' => [],
        'total' => null,
    ];

    $electronic = $quotation['electronic'] ?? [
        'summary' => [],
        'standalone' => [],
        'networks' => [],
        'total' => null,
    ];

    $outdoor = $quotation['outdoor'] ?? [];

    $outdoorTypes = ['mural', 'rooftop', 'tunnel', 'bridge', 'unipole'];

    $hasFlex = count($flex['summary'] ?? []) > 0;
    $hasElectronic = count($electronic['summary'] ?? []) > 0;

    /*
     * Same row limits used by the current React preview.
     */
    $flexDetailPages = $hasFlex
        ? $buildGroupedDetailPages($flex['details'] ?? [], 18)
        : [];

    $electronicStandalonePages = $hasElectronic
        ? $buildGroupedDetailPages($electronic['standalone'] ?? [], 12)
        : [];

    $electronicNetworks = $electronic['networks'] ?? [];
    $hasElectronicNetworks = count($electronicNetworks) > 0;

    $electronicNetworkGroups = [];

    foreach ($electronicNetworks as $network) {
        $networkGroup = $network;
        $networkGroup['items'] = $network['screens'] ?? [];
        $electronicNetworkGroups[] = $networkGroup;
    }

    $electronicNetworkPages = $hasElectronicNetworks
        ? $buildGroupedDetailPages($electronicNetworkGroups, 12)
        : [];

    $outdoorSections = [];

    foreach ($outdoorTypes as $type) {
        $data = $outdoor[$type] ?? [
            'summary' => [],
            'details' => [],
            'total' => null,
        ];

        if (count($data['summary'] ?? []) === 0) {
            continue;
        }

        $outdoorSections[] = [
            'type' => $type,
            'data' => $data,
            'detailPages' => $buildGroupedDetailPages($data['details'] ?? [], 14),
        ];
    }

    /*
     * Same final-summary composition as the React preview.
     */
    $finalSummaryItems = [];

    if ($hasFlex) {
        $finalSummaryItems[] = [
            'key' => 'flex',
            'label' => $t['finalSummary']['flex'],
            'value' => $flex['total'],
        ];
    }

    if ($hasElectronic) {
        $finalSummaryItems[] = [
            'key' => 'electronic',
            'label' => $t['finalSummary']['electronic'],
            'value' => $electronic['total'],
        ];
    }

    foreach ($outdoorSections as $section) {
        $typeLabel = $t['outdoor']['types'][$section['type']];

        $finalSummaryItems[] = [
            'key' => $section['type'],
            'label' => $replace(
                $t['finalSummary']['outdoor'],
                ['type' => $typeLabel],
            ),
            'value' => $section['data']['total'],
        ];
    }

    /*
     * Same page-number calculation as QuotationDocument.jsx.
     */
    $flexDetailsStartPage = 3;

    $electronicSummaryPage = $hasFlex
        ? $flexDetailsStartPage + count($flexDetailPages)
        : 2;

    $electronicStandaloneStartPage = $hasFlex
        ? $electronicSummaryPage + 1
        : 3;

    $electronicNetworksStartPage =
        $electronicStandaloneStartPage + count($electronicStandalonePages);

    $outdoorStartPage = $hasElectronic
        ? $electronicNetworksStartPage + count($electronicNetworkPages)
        : ($hasFlex
            ? $flexDetailsStartPage + count($flexDetailPages)
            : 3);

    $outdoorSectionsWithPages = [];
    $nextOutdoorPage = $outdoorStartPage;

    foreach ($outdoorSections as $section) {
        $summaryPage = $nextOutdoorPage;
        $detailsStartPage = $summaryPage + 1;

        $section['summaryPage'] = $summaryPage;
        $section['detailsStartPage'] = $detailsStartPage;

        $outdoorSectionsWithPages[] = $section;

        $nextOutdoorPage =
            $detailsStartPage + count($section['detailPages']);
    }

    $finalSummaryPage = $nextOutdoorPage;

    $hasFinalAmount = $finalAmount !== null && $finalAmount !== '';

    /*
     * Common header/footer renderers for every internal page.
     */
    $renderPageHeader = static function () use (
        $t,
        $documentDate,
        $logoColored
    ): string {
        $title = e($t['title']);
        $date = e($documentDate);
        $logo = e($logoColored);

        return <<<HTML
<header class="page-header">
    <div class="page-header-row">
        <div class="page-header-copy">
            <p class="page-header-title">{$title}</p>
            <p class="page-header-date">{$date}</p>
        </div>
        <img class="page-header-logo" src="{$logo}" alt="Blink">
    </div>
</header>
HTML;
    };

    $renderPageFooter = static function (int $pageNumber) use (
        $t,
        $customerName,
        $replace
    ): string {
        $customer = e($customerName);
        $pageLabel = e(
            $replace($t['pageNumber'], ['page' => $pageNumber])
        );

        return <<<HTML
<footer class="page-footer">
    <div class="page-footer-line"></div>
    <div class="page-footer-row">
        <p class="page-footer-customer">{$customer}</p>
        <div class="page-footer-accent"></div>
        <p class="page-footer-number" dir="ltr">{$pageLabel}</p>
    </div>
</footer>
HTML;
    };
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

        /*
         * Tailwind preflight-equivalent reset for the elements used by the
         * existing quotation document. This keeps sizing/spacing identical.
         */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: currentColor;
        }

        html {
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            tab-size: 4;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 210mm;
            background: #ffffff;
            color: #101111;
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
            min-height: 100%;
            line-height: inherit;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
            font-size: inherit;
            font-weight: inherit;
        }

        table {
            border-collapse: collapse;
            border-color: inherit;
            text-indent: 0;
        }

        img,
        svg {
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

        /* ==============================================================
           Cover — exact translation of QuotationCover.jsx
           ============================================================== */

        .cover-brand-header {
            position: relative;
            height: 300px;
            overflow: hidden;
            padding: 48px 64px;
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    #2d1264 0%,
                    #3b1775 58%,
                    #6525a8 100%
                );
        }

        .cover-symbol-top {
            pointer-events: none;
            position: absolute;
            inset-inline-end: -80px;
            bottom: -80px;
            width: 320px;
            max-width: none;
            opacity: 0.08;
            filter: brightness(0) invert(1);
        }

        .cover-logo {
            position: relative;
            z-index: 10;
            width: 180px;
            height: auto;
            margin-inline: auto;
            object-fit: contain;
        }

        .cover-title-block {
            position: relative;
            z-index: 10;
            margin-top: 64px;
        }

        .cover-title {
            color: #ffffff;
            font-size: 44px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .cover-title-accent {
            width: 64px;
            height: 3px;
            margin-top: 20px;
            border-radius: 9999px;
            background: #8801fe;
        }

        .cover-body {
            position: relative;
            padding: 64px 64px 0;
        }

        .cover-prepared-label {
            color: #9b929e;
            font-size: 16px;
            font-weight: 600;
        }

        .cover-customer {
            max-width: 560px;
            margin-top: 12px;
            color: #312734;
            font-size: 40px;
            font-weight: 700;
            line-height: 1.625;
        }

        .cover-metadata {
            max-width: 260px;
            margin-top: 48px;
        }

        .cover-metadata-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .cover-metadata-item {
            padding: 20px 24px;
            border: 1px solid #e7e1ea;
            border-radius: 12px;
            background: #fbf9fc;
        }

        .cover-metadata-label {
            color: #9c939f;
            font-size: 10px;
            font-weight: 600;
        }

        .cover-metadata-value {
            margin-top: 12px;
            color: #312734;
            font-size: 20px;
            font-weight: 700;
            line-height: 1;
            text-align: center;
        }

        .cover-symbol-bottom {
            pointer-events: none;
            position: absolute;
            inset-inline-end: -56px;
            bottom: -60px;
            width: 288px;
            max-width: none;
            opacity: 0.1;
        }

        /* ==============================================================
           Internal page chrome — exact translation of
           QuotationDocumentPage.jsx
           ============================================================== */

        .page-header {
            padding: 32px 48px 12px;
            border-bottom: 1px solid #e8e2ec;
        }

        .page-header-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 32px;
        }

        .page-header-copy {
            min-width: 0;
        }

        .page-header-title {
            color: #322737;
            font-size: 18px;
            font-weight: 700;
        }

        .page-header-date {
            margin-top: 4px;
            color: #9b919f;
            font-size: 10px;
            font-weight: 500;
        }

        .page-header-logo {
            width: 112px;
            height: auto;
            object-fit: contain;
        }

        .page-content {
            padding: 40px 48px 96px;
        }

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

        .page-footer-customer {
            min-width: 0;
            overflow: hidden;
            color: #8f8594;
            font-size: 9.5px;
            font-weight: 500;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .page-footer-accent {
            flex-shrink: 0;
            width: 48px;
            height: 3px;
            border-radius: 9999px;
            background: #8801fe;
        }

        .page-footer-number {
            flex-shrink: 0;
            color: #8f8594;
            font-size: 9.5px;
            font-weight: 600;
        }

        /* ==============================================================
           Intro
           ============================================================== */

        .intro-recipient {
            color: #332836;
            font-size: 16px;
            font-weight: 700;
            line-height: 28px;
        }

        .intro-message {
            margin-top: 4px;
            color: #675d6a;
            font-size: 14px;
            font-weight: 500;
            line-height: 32px;
        }

        .intro-offer-type {
            margin-top: 16px;
            color: #000000;
            font-size: 14px;
            font-weight: 600;
        }

        /*
         * Page 2 spacing is intentionally different:
         * Flex has the wrapper mt-10 + its own mt-10 = 80px.
         * Electronic fallback has only the wrapper mt-10 = 40px.
         */
        .intro-flex-summary {
            margin-top: 80px;
        }

        .intro-electronic-summary {
            margin-top: 40px;
        }

        /* ==============================================================
           Shared titles
           ============================================================== */

        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .section-title-accent {
            flex-shrink: 0;
            width: 24px;
            height: 3px;
            border-radius: 9999px;
            background: #8801fe;
        }

        .section-title-text {
            color: #372b3b;
            font-size: 14px;
            font-weight: 700;
        }

        .group-title {
            margin-bottom: 12px;
            color: #992dff;
            font-size: 12px;
            font-weight: 700;
        }

        .detail-group + .detail-group {
            margin-top: 32px;
        }

        /* ==============================================================
           Shared quotation table
           ============================================================== */

        .quotation-table-shell {
            overflow: hidden;
            border: 1px solid #ddd5e3;
            border-radius: 8px;
        }

        .quotation-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            text-align: center;
        }

        .quotation-table th {
            padding: 12px 10px;
            border: 1px solid #ddd5e3;
            background: #f1e8f6;
            color: #4b3a52;
            font-size: 10px;
            font-weight: 700;
            line-height: 16px;
        }

        .quotation-table td {
            padding: 12px 10px;
            border: 1px solid #e1d9e6;
            color: #000000;
            font-size: 10px;
            font-weight: 500;
            line-height: 16px;
        }

        .quotation-table tbody tr:nth-child(even) {
            background: #fcf9fe;
        }

        .quotation-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .quotation-table .cell-strong {
            font-weight: 700;
        }

        .quotation-table .cell-start {
            overflow-wrap: break-word;
            text-align: start;
            line-height: 20px;
        }

        .quotation-table .cell-nowrap {
            white-space: nowrap;
        }

        .quotation-table .cell-period-small {
            font-size: 9px;
        }

        /* Flex details */
        .flex-col-id {
            width: 14%;
        }

        .flex-col-location {
            width: 62%;
        }

        .flex-col-size {
            width: 14%;
        }

        .flex-col-period {
            width: 10%;
        }

        /* Electronic summary */
        .electronic-summary-col-governorate {
            width: 18%;
        }

        .electronic-summary-col-screens {
            width: 13%;
        }

        .electronic-summary-col-networks {
            width: 13%;
        }

        .electronic-summary-col-period {
            width: 32%;
        }

        .electronic-summary-col-total {
            width: 24%;
        }

        /* Electronic standalone */
        .electronic-standalone-col-id {
            width: 9%;
        }

        .electronic-standalone-col-location {
            width: 27%;
        }

        .electronic-standalone-col-size {
            width: 11%;
        }

        .electronic-standalone-col-resolution {
            width: 13%;
        }

        .electronic-standalone-col-period {
            width: 20%;
        }

        .electronic-standalone-col-slides {
            width: 8%;
        }

        .electronic-standalone-col-price {
            width: 12%;
        }

        /* Electronic network */
        .electronic-network-col-id {
            width: 10%;
        }

        .electronic-network-col-location {
            width: 34%;
        }

        .electronic-network-col-size {
            width: 12%;
        }

        .electronic-network-col-resolution {
            width: 14%;
        }

        .electronic-network-col-period {
            width: 20%;
        }

        .electronic-network-col-slides {
            width: 10%;
        }

        /* Outdoor summary */
        .outdoor-summary-col-governorate {
            width: 20%;
        }

        .outdoor-summary-col-count {
            width: 15%;
        }

        .outdoor-summary-col-period {
            width: 40%;
        }

        .outdoor-summary-col-total {
            width: 25%;
        }

        /* Outdoor details */
        .outdoor-details-col-id {
            width: 12%;
        }

        .outdoor-details-col-location {
            width: 38%;
        }

        .outdoor-details-col-size {
            width: 14%;
        }

        .outdoor-details-col-period {
            width: 22%;
        }

        .outdoor-details-col-price {
            width: 14%;
        }

        /* ==============================================================
           Section totals
           ============================================================== */

        .section-total {
            margin-top: 32px;
        }

        .section-total-box {
            display: flex;
            align-items: stretch;
            background: #f7f1fa;
        }

        .section-total-accent {
            flex-shrink: 0;
            width: 4px;
            background: #8801fe;
        }

        .section-total-content {
            display: flex;
            flex: 1 1 0%;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            padding: 16px 20px;
        }

        .section-total-label {
            color: #382d3c;
            font-size: 13px;
            font-weight: 700;
        }

        .section-total-value {
            flex-shrink: 0;
            color: #2b2230;
            font-size: 19px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        /* ==============================================================
           Electronic network total
           ============================================================== */

        .network-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-top: 8px;
            padding: 12px 16px;
            border-inline-start: 3px solid #8801fe;
            background: #faf7fc;
        }

        .network-total-label {
            color: #000000;
            font-size: 10px;
            font-weight: 700;
        }

        .network-total-value {
            flex-shrink: 0;
            color: #2f2632;
            font-size: 13px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        /* ==============================================================
           Final summary
           ============================================================== */

        .final-summary-table-shell {
            margin-top: 24px;
            overflow: hidden;
            border: 1px solid #d7d0db;
        }

        .final-summary-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .final-summary-table thead tr {
            background: #f3eef6;
        }

        .final-summary-table th {
            padding: 14px 20px;
            color: #4b3f50;
            font-size: 10px;
            font-weight: 700;
        }

        .final-summary-label-head {
            width: 68%;
            border-inline-end: 1px solid #d7d0db;
            text-align: start;
        }

        .final-summary-value-head {
            width: 32%;
            text-align: center;
        }

        .final-summary-row {
            border-top: 1px solid #e5dfe8;
        }

        .final-summary-row-label {
            padding: 16px 20px;
            border-inline-end: 1px solid #e5dfe8;
            color: #000000;
            font-size: 12px;
            font-weight: 600;
            text-align: start;
        }

        .final-summary-row-value {
            padding: 16px 20px;
            color: #000000;
            font-size: 14px;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            text-align: center;
        }

        .final-summary-grand-row {
            border-top: 2px solid #8d8192;
            background: #faf8fb;
        }

        .final-summary-grand-label {
            padding: 20px;
            border-inline-end: 1px solid #d7d0db;
            color: #302733;
            font-size: 14px;
            font-weight: 700;
            text-align: start;
        }

        .final-summary-grand-value {
            padding: 20px;
            color: #2a222d;
            font-size: 16px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: center;
        }

        .final-summary-discount-row {
            border-top: 1px solid #8801fe;
            background: #f5eef9;
        }

        .final-summary-discount-label {
            padding: 20px;
            border-inline-end: 1px solid #d7d0db;
            color: #2f1660;
            font-size: 16px;
            font-weight: 700;
            text-align: start;
        }

        .final-summary-discount-value {
            padding: 20px;
            color: #2b2230;
            font-size: 20px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: center;
        }

        .customer-signature {
            width: 256px;
            margin-top: 96px;
        }

        .customer-signature-label {
            color: #514655;
            font-size: 11px;
            font-weight: 700;
        }

        .customer-signature-line {
            margin-top: 64px;
            border-bottom: 1px solid #8f8594;
        }
    </style>
</head>

<body>
    {{-- ============================================================
         Page 1 — Cover
         ============================================================ --}}
    <section class="pdf-page">
        <header class="cover-brand-header">
            <img
                class="cover-symbol-top"
                src="{{ $brandSymbol }}"
                alt=""
                aria-hidden="true"
            >

            <img
                class="cover-logo"
                src="{{ $logoWhite }}"
                alt="Blink"
            >

            <div class="cover-title-block">
                <h1 class="cover-title">{{ $t['title'] }}</h1>
                <div class="cover-title-accent"></div>
            </div>
        </header>

        <div class="cover-body">
            <section>
                <p class="cover-prepared-label">
                    {{ $t['preparedFor'] }}
                </p>

                <h2 class="cover-customer">
                    {{ $customerName }}
                </h2>
            </section>

            <section class="cover-metadata">
                <div class="cover-metadata-stack">
                    <div class="cover-metadata-item">
                        <p class="cover-metadata-label">
                            {{ $t['advertiserType'] }}
                        </p>

                        <p class="cover-metadata-value">
                            {{ $advertiserTypeLabel }}
                        </p>
                    </div>

                    <div class="cover-metadata-item">
                        <p class="cover-metadata-label">
                            {{ $t['issueDate'] }}
                        </p>

                        <p
                            class="cover-metadata-value"
                            dir="ltr"
                        >
                            {{ $documentDate }}
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <img
            class="cover-symbol-bottom"
            src="{{ $brandSymbol }}"
            alt=""
            aria-hidden="true"
        >
    </section>

    {{-- ============================================================
         Page 2 — Intro + first available summary
         ============================================================ --}}
    <section class="pdf-page">
        {!! $renderPageHeader() !!}

        <div class="page-content">
            <section>
                <p class="intro-recipient">
                    {{
                        $replace(
                            $t['intro']['recipient'],
                            ['customer' => $customerName]
                        )
                    }}
                </p>

                <p class="intro-message">
                    {{ $t['intro']['message'] }}
                </p>

                <p class="intro-offer-type">
                    {{
                        $replace(
                            $t['intro']['offerType'],
                            ['type' => $advertiserTypeLabel]
                        )
                    }}
                </p>
            </section>

            @if ($hasFlex)
                <section class="intro-flex-summary">
                    <div class="section-title">
                        <div class="section-title-accent"></div>
                        <h2 class="section-title-text">
                            {{ $t['flex']['summaryTitle'] }}
                        </h2>
                    </div>

                    <div class="quotation-table-shell">
                        <table class="quotation-table">
                            <thead>
                                <tr>
                                    <th>{{ $t['flex']['columns']['governorate'] }}</th>
                                    <th>{{ $t['flex']['columns']['boardsCount'] }}</th>
                                    <th>{{ $t['flex']['columns']['networksCount'] }}</th>
                                    <th>{{ $t['flex']['columns']['periodNumber'] }}</th>
                                    <th>{{ $t['flex']['columns']['networkPrice'] }}</th>
                                    <th>{{ $t['flex']['columns']['totalPrice'] }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($flex['summary'] as $row)
                                    <tr>
                                        <td>{{ $row['governorate']['name'] }}</td>
                                        <td>{{ $row['boards_count'] }}</td>
                                        <td>{{ $row['networks_count'] }}</td>
                                        <td>{{ $row['period']['number'] }}</td>
                                        <td dir="ltr">
                                            {{ $formatUsd($row['network_price']) }}
                                        </td>
                                        <td
                                            dir="ltr"
                                            class="cell-strong"
                                        >
                                            {{ $formatUsd($row['total_price']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @elseif ($hasElectronic)
                <section class="intro-electronic-summary">
                    <div class="section-title">
                        <div class="section-title-accent"></div>
                        <h2 class="section-title-text">
                            {{ $t['electronic']['summaryTitle'] }}
                        </h2>
                    </div>

                    <div class="quotation-table-shell">
                        <table class="quotation-table">
                            <thead>
                                <tr>
                                    <th class="electronic-summary-col-governorate">
                                        {{ $t['electronic']['columns']['governorate'] }}
                                    </th>
                                    <th class="electronic-summary-col-screens">
                                        {{ $t['electronic']['columns']['screensCount'] }}
                                    </th>
                                    <th class="electronic-summary-col-networks">
                                        {{ $t['electronic']['columns']['networksCount'] }}
                                    </th>
                                    <th class="electronic-summary-col-period">
                                        {{ $t['electronic']['columns']['period'] }}
                                    </th>
                                    <th class="electronic-summary-col-total">
                                        {{ $t['electronic']['columns']['totalPrice'] }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($electronic['summary'] as $row)
                                    <tr>
                                        <td>{{ $row['governorate']['name'] }}</td>
                                        <td>{{ $row['screens_count'] }}</td>
                                        <td>{{ $row['networks_count'] }}</td>
                                        <td
                                            dir="ltr"
                                            class="cell-nowrap"
                                        >
                                            {{ $formatDateRange($row['period']) }}
                                        </td>
                                        <td
                                            dir="ltr"
                                            class="cell-strong cell-nowrap"
                                        >
                                            {{ $formatUsd($row['total_price']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        {!! $renderPageFooter(2) !!}
    </section>

    {{-- ============================================================
         Flex detail pages
         ============================================================ --}}
    @foreach ($flexDetailPages as $pageIndex => $pageGroups)
        @php
            $pageNumber = $flexDetailsStartPage + $pageIndex;
            $isFirstPage = $pageIndex === 0;
            $isLastPage = $pageIndex === count($flexDetailPages) - 1;
        @endphp

        <section class="pdf-page">
            {!! $renderPageHeader() !!}

            <div class="page-content">
                @foreach ($pageGroups as $groupIndex => $group)
                    <div class="detail-group">
                        @if ($isFirstPage && $groupIndex === 0)
                            <div class="section-title">
                                <div class="section-title-accent"></div>
                                <h2 class="section-title-text">
                                    {{ $t['flex']['detailsTitle'] }}
                                </h2>
                            </div>
                        @endif

                        <h3 class="group-title">
                            {{
                                $replace(
                                    $t['flex']['governorateDetails'],
                                    [
                                        'governorate' =>
                                            $group['governorate']['name'],
                                    ],
                                )
                            }}
                        </h3>

                        <div class="quotation-table-shell">
                            <table class="quotation-table">
                                <thead>
                                    <tr>
                                        <th class="flex-col-id">
                                            {{ $t['flex']['columns']['id'] }}
                                        </th>
                                        <th class="flex-col-location">
                                            {{ $t['flex']['columns']['location'] }}
                                        </th>
                                        <th class="flex-col-size">
                                            {{ $t['flex']['columns']['size'] }}
                                        </th>
                                        <th class="flex-col-period">
                                            {{ $t['flex']['columns']['periodNumber'] }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($group['items'] as $item)
                                        <tr>
                                            <td>{{ $item['id'] }}</td>
                                            <td>{{ $item['location'] }}</td>
                                            <td dir="ltr">
                                                {{
                                                    $formatSize(
                                                        $item['width'],
                                                        $item['height'],
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{ $item['period']['number'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                @if ($isLastPage)
                    <div class="section-total">
                        <div class="section-total-box">
                            <div class="section-total-accent"></div>

                            <div class="section-total-content">
                                <p class="section-total-label">
                                    {{ $t['flex']['total'] }}
                                </p>

                                <p
                                    class="section-total-value"
                                    dir="ltr"
                                >
                                    {{ $formatUsd($flex['total']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {!! $renderPageFooter($pageNumber) !!}
        </section>
    @endforeach

    {{-- ============================================================
         Electronic summary on its own page when Flex exists
         ============================================================ --}}
    @if ($hasElectronic && $hasFlex)
        <section class="pdf-page">
            {!! $renderPageHeader() !!}

            <div class="page-content">
                <section>
                    <div class="section-title">
                        <div class="section-title-accent"></div>
                        <h2 class="section-title-text">
                            {{ $t['electronic']['summaryTitle'] }}
                        </h2>
                    </div>

                    <div class="quotation-table-shell">
                        <table class="quotation-table">
                            <thead>
                                <tr>
                                    <th class="electronic-summary-col-governorate">
                                        {{ $t['electronic']['columns']['governorate'] }}
                                    </th>
                                    <th class="electronic-summary-col-screens">
                                        {{ $t['electronic']['columns']['screensCount'] }}
                                    </th>
                                    <th class="electronic-summary-col-networks">
                                        {{ $t['electronic']['columns']['networksCount'] }}
                                    </th>
                                    <th class="electronic-summary-col-period">
                                        {{ $t['electronic']['columns']['period'] }}
                                    </th>
                                    <th class="electronic-summary-col-total">
                                        {{ $t['electronic']['columns']['totalPrice'] }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($electronic['summary'] as $row)
                                    <tr>
                                        <td>{{ $row['governorate']['name'] }}</td>
                                        <td>{{ $row['screens_count'] }}</td>
                                        <td>{{ $row['networks_count'] }}</td>
                                        <td
                                            dir="ltr"
                                            class="cell-nowrap"
                                        >
                                            {{ $formatDateRange($row['period']) }}
                                        </td>
                                        <td
                                            dir="ltr"
                                            class="cell-strong cell-nowrap"
                                        >
                                            {{ $formatUsd($row['total_price']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {!! $renderPageFooter($electronicSummaryPage) !!}
        </section>
    @endif

    {{-- ============================================================
         Electronic standalone pages
         ============================================================ --}}
    @foreach ($electronicStandalonePages as $pageIndex => $pageGroups)
        @php
            $pageNumber =
                $electronicStandaloneStartPage + $pageIndex;

            $isLastStandalonePage =
                $pageIndex === count($electronicStandalonePages) - 1;
        @endphp

        <section class="pdf-page">
            {!! $renderPageHeader() !!}

            <div class="page-content">
                @foreach ($pageGroups as $groupIndex => $group)
                    <div class="detail-group">
                        @if ($pageIndex === 0 && $groupIndex === 0)
                            <div class="section-title">
                                <div class="section-title-accent"></div>
                                <h2 class="section-title-text">
                                    {{ $t['electronic']['standaloneTitle'] }}
                                </h2>
                            </div>
                        @endif

                        <h3 class="group-title">
                            {{
                                $replace(
                                    $t['electronic']['governorateDetails'],
                                    [
                                        'governorate' =>
                                            $group['governorate']['name'],
                                    ],
                                )
                            }}
                        </h3>

                        <div class="quotation-table-shell">
                            <table class="quotation-table">
                                <thead>
                                    <tr>
                                        <th class="electronic-standalone-col-id">
                                            {{ $t['electronic']['columns']['id'] }}
                                        </th>
                                        <th class="electronic-standalone-col-location">
                                            {{ $t['electronic']['columns']['location'] }}
                                        </th>
                                        <th class="electronic-standalone-col-size">
                                            {{ $t['electronic']['columns']['size'] }}
                                        </th>
                                        <th class="electronic-standalone-col-resolution">
                                            {{ $t['electronic']['columns']['resolution'] }}
                                        </th>
                                        <th class="electronic-standalone-col-period">
                                            {{ $t['electronic']['columns']['period'] }}
                                        </th>
                                        <th class="electronic-standalone-col-slides">
                                            {{ $t['electronic']['columns']['slidesCount'] }}
                                        </th>
                                        <th class="electronic-standalone-col-price">
                                            {{ $t['electronic']['columns']['price'] }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($group['items'] as $item)
                                        <tr>
                                            <td
                                                dir="ltr"
                                                class="cell-nowrap"
                                            >
                                                {{ $item['id'] }}
                                            </td>

                                            <td class="cell-start">
                                                {{ $item['location'] }}
                                            </td>

                                            <td
                                                dir="ltr"
                                                class="cell-nowrap"
                                            >
                                                {{
                                                    $formatSize(
                                                        $item['width'],
                                                        $item['height'],
                                                    )
                                                }}
                                            </td>

                                            <td
                                                dir="ltr"
                                                class="cell-nowrap"
                                            >
                                                {{ $item['resolution'] }}
                                            </td>

                                            <td
                                                dir="ltr"
                                                class="cell-nowrap cell-period-small"
                                            >
                                                {{
                                                    $formatDateRange(
                                                        $item['period']
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{ $item['slides_count'] }}
                                            </td>

                                            <td
                                                dir="ltr"
                                                class="cell-strong cell-nowrap"
                                            >
                                                {{ $formatUsd($item['price']) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                @if ($isLastStandalonePage && !$hasElectronicNetworks)
                    <div class="section-total">
                        <div class="section-total-box">
                            <div class="section-total-accent"></div>

                            <div class="section-total-content">
                                <p class="section-total-label">
                                    {{ $t['electronic']['total'] }}
                                </p>

                                <p
                                    class="section-total-value"
                                    dir="ltr"
                                >
                                    {{ $formatUsd($electronic['total']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {!! $renderPageFooter($pageNumber) !!}
        </section>
    @endforeach

    {{-- ============================================================
         Electronic network pages
         ============================================================ --}}
    @foreach ($electronicNetworkPages as $pageIndex => $pageNetworks)
        @php
            $pageNumber =
                $electronicNetworksStartPage + $pageIndex;

            $isLastNetworkPage =
                $pageIndex === count($electronicNetworkPages) - 1;
        @endphp

        <section class="pdf-page">
            {!! $renderPageHeader() !!}

            <div class="page-content">
                @foreach ($pageNetworks as $groupIndex => $network)
                    <div class="detail-group">
                        @if ($pageIndex === 0 && $groupIndex === 0)
                            <div class="section-title">
                                <div class="section-title-accent"></div>
                                <h2 class="section-title-text">
                                    {{ $t['electronic']['networksTitle'] }}
                                </h2>
                            </div>
                        @endif

                        <h3 class="group-title">
                            {{
                                $replace(
                                    $t['electronic']['networkTitle'],
                                    [
                                        'network' => $network['name'],
                                        'governorate' =>
                                            $network['governorate']['name'],
                                    ],
                                )
                            }}
                        </h3>

                        <div class="quotation-table-shell">
                            <table class="quotation-table">
                                <thead>
                                    <tr>
                                        <th class="electronic-network-col-id">
                                            {{ $t['electronic']['columns']['id'] }}
                                        </th>
                                        <th class="electronic-network-col-location">
                                            {{ $t['electronic']['columns']['location'] }}
                                        </th>
                                        <th class="electronic-network-col-size">
                                            {{ $t['electronic']['columns']['size'] }}
                                        </th>
                                        <th class="electronic-network-col-resolution">
                                            {{ $t['electronic']['columns']['resolution'] }}
                                        </th>
                                        <th class="electronic-network-col-period">
                                            {{ $t['electronic']['columns']['period'] }}
                                        </th>
                                        <th class="electronic-network-col-slides">
                                            {{ $t['electronic']['columns']['slidesCount'] }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($network['items'] as $item)
                                        <tr>
                                            <td
                                                dir="ltr"
                                                class="cell-nowrap"
                                            >
                                                {{ $item['id'] }}
                                            </td>

                                            <td class="cell-start">
                                                {{ $item['location'] }}
                                            </td>

                                            <td
                                                dir="ltr"
                                                class="cell-nowrap"
                                            >
                                                {{
                                                    $formatSize(
                                                        $item['width'],
                                                        $item['height'],
                                                    )
                                                }}
                                            </td>

                                            <td
                                                dir="ltr"
                                                class="cell-nowrap"
                                            >
                                                {{ $item['resolution'] }}
                                            </td>

                                            <td
                                                dir="ltr"
                                                class="cell-nowrap cell-period-small"
                                            >
                                                {{
                                                    $formatDateRange(
                                                        $network['period']
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{ $item['slides_count'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($network['isLastChunk'])
                            <div class="network-total">
                                <p class="network-total-label">
                                    {{ $t['electronic']['networkTotal'] }}
                                </p>

                                <p
                                    class="network-total-value"
                                    dir="ltr"
                                >
                                    {{
                                        $formatUsd(
                                            $network['total_price']
                                        )
                                    }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach

                @if ($isLastNetworkPage)
                    <div class="section-total">
                        <div class="section-total-box">
                            <div class="section-total-accent"></div>

                            <div class="section-total-content">
                                <p class="section-total-label">
                                    {{ $t['electronic']['total'] }}
                                </p>

                                <p
                                    class="section-total-value"
                                    dir="ltr"
                                >
                                    {{ $formatUsd($electronic['total']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {!! $renderPageFooter($pageNumber) !!}
        </section>
    @endforeach

    {{-- ============================================================
         Outdoor types
         ============================================================ --}}
    @foreach ($outdoorSectionsWithPages as $section)
        @php
            $type = $section['type'];
            $typeLabel = $t['outdoor']['types'][$type];
            $data = $section['data'];
            $detailPages = $section['detailPages'];
        @endphp

        {{-- Outdoor summary --}}
        <section class="pdf-page">
            {!! $renderPageHeader() !!}

            <div class="page-content">
                <section>
                    <div class="section-title">
                        <div class="section-title-accent"></div>
                        <h2 class="section-title-text">
                            {{
                                $replace(
                                    $t['outdoor']['summaryTitle'],
                                    ['type' => $typeLabel],
                                )
                            }}
                        </h2>
                    </div>

                    <div class="quotation-table-shell">
                        <table class="quotation-table">
                            <thead>
                                <tr>
                                    <th class="outdoor-summary-col-governorate">
                                        {{ $t['outdoor']['columns']['governorate'] }}
                                    </th>
                                    <th class="outdoor-summary-col-count">
                                        {{ $t['outdoor']['columns']['assetsCount'] }}
                                    </th>
                                    <th class="outdoor-summary-col-period">
                                        {{ $t['outdoor']['columns']['period'] }}
                                    </th>
                                    <th class="outdoor-summary-col-total">
                                        {{ $t['outdoor']['columns']['totalPrice'] }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($data['summary'] as $row)
                                    <tr>
                                        <td>
                                            {{ $row['governorate']['name'] }}
                                        </td>

                                        <td>
                                            {{ $row['assets_count'] }}
                                        </td>

                                        <td
                                            dir="ltr"
                                            class="cell-nowrap"
                                        >
                                            {{
                                                $formatDateRange(
                                                    $row['period']
                                                )
                                            }}
                                        </td>

                                        <td
                                            dir="ltr"
                                            class="cell-strong cell-nowrap"
                                        >
                                            {{
                                                $formatUsd(
                                                    $row['total_price']
                                                )
                                            }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (count($detailPages) === 0)
                        <div class="section-total">
                            <div class="section-total-box">
                                <div class="section-total-accent"></div>

                                <div class="section-total-content">
                                    <p class="section-total-label">
                                        {{
                                            $replace(
                                                $t['outdoor']['total'],
                                                ['type' => $typeLabel],
                                            )
                                        }}
                                    </p>

                                    <p
                                        class="section-total-value"
                                        dir="ltr"
                                    >
                                        {{ $formatUsd($data['total']) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            {!! $renderPageFooter($section['summaryPage']) !!}
        </section>

        {{-- Outdoor details --}}
        @foreach ($detailPages as $pageIndex => $pageGroups)
            @php
                $pageNumber =
                    $section['detailsStartPage'] + $pageIndex;

                $isLastPage =
                    $pageIndex === count($detailPages) - 1;
            @endphp

            <section class="pdf-page">
                {!! $renderPageHeader() !!}

                <div class="page-content">
                    @foreach ($pageGroups as $groupIndex => $group)
                        <div class="detail-group">
                            @if ($pageIndex === 0 && $groupIndex === 0)
                                <div class="section-title">
                                    <div class="section-title-accent"></div>
                                    <h2 class="section-title-text">
                                        {{
                                            $replace(
                                                $t['outdoor']['detailsTitle'],
                                                ['type' => $typeLabel],
                                            )
                                        }}
                                    </h2>
                                </div>
                            @endif

                            <h3 class="group-title">
                                {{
                                    $replace(
                                        $t['outdoor']['governorateDetails'],
                                        [
                                            'governorate' =>
                                                $group['governorate']['name'],
                                        ],
                                    )
                                }}
                            </h3>

                            <div class="quotation-table-shell">
                                <table class="quotation-table">
                                    <thead>
                                        <tr>
                                            <th class="outdoor-details-col-id">
                                                {{ $t['outdoor']['columns']['id'] }}
                                            </th>
                                            <th class="outdoor-details-col-location">
                                                {{ $t['outdoor']['columns']['location'] }}
                                            </th>
                                            <th class="outdoor-details-col-size">
                                                {{ $t['outdoor']['columns']['size'] }}
                                            </th>
                                            <th class="outdoor-details-col-period">
                                                {{ $t['outdoor']['columns']['period'] }}
                                            </th>
                                            <th class="outdoor-details-col-price">
                                                {{ $t['outdoor']['columns']['price'] }}
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($group['items'] as $item)
                                            <tr>
                                                <td
                                                    dir="ltr"
                                                    class="cell-nowrap"
                                                >
                                                    {{ $item['id'] }}
                                                </td>

                                                <td class="cell-start">
                                                    {{ $item['location'] }}
                                                </td>

                                                <td
                                                    dir="ltr"
                                                    class="cell-nowrap"
                                                >
                                                    {{
                                                        $formatSize(
                                                            $item['width'],
                                                            $item['height'],
                                                        )
                                                    }}
                                                </td>

                                                <td
                                                    dir="ltr"
                                                    class="cell-nowrap cell-period-small"
                                                >
                                                    {{
                                                        $formatDateRange(
                                                            $item['period']
                                                        )
                                                    }}
                                                </td>

                                                <td
                                                    dir="ltr"
                                                    class="cell-strong cell-nowrap"
                                                >
                                                    {{
                                                        $formatUsd(
                                                            $item['price']
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    @if ($isLastPage)
                        <div class="section-total">
                            <div class="section-total-box">
                                <div class="section-total-accent"></div>

                                <div class="section-total-content">
                                    <p class="section-total-label">
                                        {{
                                            $replace(
                                                $t['outdoor']['total'],
                                                ['type' => $typeLabel],
                                            )
                                        }}
                                    </p>

                                    <p
                                        class="section-total-value"
                                        dir="ltr"
                                    >
                                        {{ $formatUsd($data['total']) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {!! $renderPageFooter($pageNumber) !!}
            </section>
        @endforeach
    @endforeach

    {{-- ============================================================
         Final summary
         ============================================================ --}}
    <section class="pdf-page pdf-page--last">
        {!! $renderPageHeader() !!}

        <div class="page-content">
            <section>
                <div class="section-title">
                    <div class="section-title-accent"></div>
                    <h2 class="section-title-text">
                        {{ $t['finalSummary']['title'] }}
                    </h2>
                </div>

                <div class="final-summary-table-shell">
                    <table class="final-summary-table">
                        <thead>
                            <tr>
                                <th class="final-summary-label-head">
                                    {{ $t['finalSummary']['bookingType'] }}
                                </th>

                                <th class="final-summary-value-head">
                                    {{ $t['finalSummary']['amount'] }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($finalSummaryItems as $item)
                                <tr class="final-summary-row">
                                    <td class="final-summary-row-label">
                                        {{ $item['label'] }}
                                    </td>

                                    <td
                                        class="final-summary-row-value"
                                        dir="ltr"
                                    >
                                        {{ $formatUsd($item['value']) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="final-summary-grand-row">
                                <td class="final-summary-grand-label">
                                    {{ $t['finalSummary']['grandTotal'] }}
                                </td>

                                <td
                                    class="final-summary-grand-value"
                                    dir="ltr"
                                >
                                    {{
                                        $formatUsd(
                                            $quotation['grand_total']
                                        )
                                    }}
                                </td>
                            </tr>

                            @if ($hasFinalAmount)
                                <tr class="final-summary-discount-row">
                                    <td class="final-summary-discount-label">
                                        {{ $t['finalSummary']['finalAmount'] }}
                                    </td>

                                    <td
                                        class="final-summary-discount-value"
                                        dir="ltr"
                                    >
                                        {{ $formatUsd($finalAmount) }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="customer-signature">
                    <p class="customer-signature-label">
                        {{ $t['finalSummary']['customerSignature'] }}
                    </p>

                    <div class="customer-signature-line"></div>
                </div>
            </section>
        </div>

        {!! $renderPageFooter($finalSummaryPage) !!}
    </section>
</body>
</html>
