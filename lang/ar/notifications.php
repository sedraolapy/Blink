
<?php

return [
    'types' => [

        // Contracts
        'contract_start_reminder' => [
            'title' => 'اقتراب بداية عقد',
            'message' => 'يبدأ عقد {customer_name} بعد 7 أيام، بتاريخ {start_date}.',
        ],

        'contract_started' => [
            'title' => 'بداية عقد',
            'message' => 'بدأ اليوم عقد {customer_name}.',
        ],

        'contract_end_reminder' => [
            'title' => 'اقتراب انتهاء عقد',
            'message' => 'ينتهي عقد {customer_name} بعد 7 أيام، بتاريخ {end_date}.',
        ],

        'contract_ended' => [
            'title' => 'انتهاء عقد',
            'message' => 'انتهى اليوم عقد {customer_name}.',
        ],

        // Flex orders
        'flex_orders_reminder' => [
            'title' => 'تذكير بإصدار أوامر الفليكس',
            'message' => 'يرجى إصدار أوامر حجوزات الفليكس للزبون {name_customer} للفترة {period_number}.',
        ],

        // Electronic bookings
        'electronic_orders_reminder' => [
            'title' => 'تذكير بأوامر الحجز الإلكتروني',
            'message' => 'يرجى إصدار أوامر الحجز الإلكتروني للزبون {customer_name}؛ يبدأ الحجز بعد 3 أيام.',
        ],

        'electronic_period_started' => [
            'title' => 'بداية حجز إلكتروني',
            'message' => 'بدأت اليوم مدة الحجز الإلكتروني للزبون {customer_name}.',
        ],

        'electronic_period_ended' => [
            'title' => 'نهاية حجز إلكتروني',
            'message' => 'انتهت اليوم مدة الحجز الإلكتروني للزبون {customer_name}.',
        ],

        // Outdoor bookings
        'outdoor_orders_reminder' => [
            'title' => 'تذكير بأوامر الحجز الخارجي',
            'message' => 'يرجى إصدار أوامر حجز {outdoor_type_name} للزبون {customer_name}؛ يبدأ الحجز بعد 3 أيام.',
        ],

        'outdoor_period_started' => [
            'title' => 'بداية حجز خارجي',
            'message' => 'بدأت اليوم مدة حجز {outdoor_type_name} للزبون {customer_name}.',
        ],

        'outdoor_period_ended' => [
            'title' => 'نهاية حجز خارجي',
            'message' => 'انتهت اليوم مدة حجز {outdoor_type_name} للزبون {customer_name}.',
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
