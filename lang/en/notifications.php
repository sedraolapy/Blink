
<?php

return [
    'types' => [

        // Contracts
        'contract_start_reminder' => [
            'title' => 'Contract Starting Soon',
            'message' => 'The contract for :customer starts in 7 days, on :date.',
        ],

        'contract_started' => [
            'title' => 'Contract Started',
            'message' => 'The contract for :customer starts today.',
        ],

        'contract_end_reminder' => [
            'title' => 'Contract Ending Soon',
            'message' => 'The contract for :customer ends in 7 days, on :date.',
        ],

        'contract_ended' => [
            'title' => 'Contract Ending Today',
            'message' => 'The contract for :customer ends today.',
        ],

        // Flex orders
        'flex_orders_reminder' => [
            'title' => 'Flex Orders Reminder',
            'message' => 'Please issue Flex booking orders for :customer for period :period.',
        ],

        // Electronic bookings
        'electronic_orders_reminder' => [
            'title' => 'Electronic Booking Orders Reminder',
            'message' => 'Please issue electronic booking orders for :customer. The booking starts in 3 days.',
        ],

        'electronic_period_started' => [
            'title' => 'Electronic Booking Started',
            'message' => 'The electronic booking period for :customer starts today.',
        ],

        'electronic_period_ended' => [
            'title' => 'Electronic Booking Ended',
            'message' => 'The electronic booking period for :customer ends today.',
        ],

        // Outdoor bookings
        'outdoor_orders_reminder' => [
            'title' => 'Outdoor Booking Orders Reminder',
            'message' => 'Please issue :outdoor_type booking orders for :customer. The booking starts in 3 days.',
        ],

        'outdoor_period_started' => [
            'title' => 'Outdoor Booking Started',
            'message' => 'The :outdoor_type booking period for :customer starts today.',
        ],

        'outdoor_period_ended' => [
            'title' => 'Outdoor Booking Ended',
            'message' => 'The :outdoor_type booking period for :customer ends today.',
        ],

        // Institution reports
        'flex_institution_report_reminder' => [
            'title' => 'Institution Report - Flex',
            'message' => 'Reminder to issue the institution report for Flex period :period.',
        ],

        'monthly_institution_report_reminder' => [
            'title' => 'Monthly Institution Report',
            'message' => 'Reminder to issue the monthly institution report for electronic and outdoor bookings for :month.',
        ],
    ],
];
