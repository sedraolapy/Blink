
<?php

return [
    'types' => [

        // Contracts
        'contract_start_reminder' => [
            'title' => 'اقتراب بداية عقد',
            'message' => 'يبدأ عقد :customer بعد 7 أيام، بتاريخ :date.',
        ],

        'contract_started' => [
            'title' => 'بداية عقد',
            'message' => 'بدأ اليوم عقد :customer.',
        ],

        'contract_end_reminder' => [
            'title' => 'اقتراب انتهاء عقد',
            'message' => 'ينتهي عقد :customer بعد 7 أيام، بتاريخ :date.',
        ],

        'contract_ended' => [
            'title' => 'انتهاء عقد',
            'message' => 'ينتهي اليوم عقد :customer.',
        ],

        // Flex orders
        'flex_orders_reminder' => [
            'title' => 'تذكير بإصدار أوامر الفليكس',
            'message' => 'يرجى إصدار أوامر حجوزات الفليكس للزبون :customer للفترة :period.',
        ],

        // Electronic bookings
        'electronic_orders_reminder' => [
            'title' => 'تذكير بأوامر الحجز الإلكتروني',
            'message' => 'يرجى إصدار أوامر الحجز الإلكتروني للزبون :customer؛ يبدأ الحجز بعد 3 أيام.',
        ],

        'electronic_period_started' => [
            'title' => 'بداية حجز إلكتروني',
            'message' => 'بدأت اليوم مدة الحجز الإلكتروني للزبون :customer.',
        ],

        'electronic_period_ended' => [
            'title' => 'نهاية حجز إلكتروني',
            'message' => 'انتهت اليوم مدة الحجز الإلكتروني للزبون :customer.',
        ],

        // Outdoor bookings
        'outdoor_orders_reminder' => [
            'title' => 'تذكير بأوامر الحجز الخارجي',
            'message' => 'يرجى إصدار أوامر حجز :outdoor_type للزبون :customer؛ يبدأ الحجز بعد 3 أيام.',
        ],

        'outdoor_period_started' => [
            'title' => 'بداية حجز خارجي',
            'message' => 'بدأت اليوم مدة حجز :outdoor_type للزبون :customer.',
        ],

        'outdoor_period_ended' => [
            'title' => 'نهاية حجز خارجي',
            'message' => 'انتهت اليوم مدة حجز :outdoor_type للزبون :customer.',
        ],

        // Institution reports
        'flex_institution_report_reminder' => [
            'title' => 'تقرير المؤسسة - الفليكس',
            'message' => 'تذكير بإصدار تقرير المؤسسة لفترة الفليكس رقم :period.',
        ],

        'monthly_institution_report_reminder' => [
            'title' => 'تقرير المؤسسة الشهري',
            'message' => 'تذكير بإصدار تقرير المؤسسة الشهري للإلكتروني والخارجي لشهر :month.',
        ],
    ],
];
