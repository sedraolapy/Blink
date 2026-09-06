<?php

return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'string' => 'The :attribute must be a string.',
    'unique' => 'The :attribute has already been taken.',

    'attributes' => [
        'email' => 'email',
        'password' => 'password',
        'customer_name' => 'customer name',
        'customer_name_ar' => 'customer name in Arabic',
        'customer_name_en' => 'customer name in English',
        'phone' => 'phone number',
        'search' => 'search',
        'status' => 'status',
        'governorate_id' => 'governorate',
        'period_id' => 'advertising period',
        'page' => 'page',
        'contract_status' => 'contract status',
        'subscription_type' => 'subscription type',
        'from_date' => 'from date',
        'to_date' => 'to date',
        'type' => 'asset type',
    ],

    'custom' => [
        'network_id' => [
            'same_area' => 'All screens within the network must belong to the same area.',
            ],
    ],

    'electronic' => [
        'search' => [
            'string' => 'The search value must be a string.',
            'max' => 'The search value must not exceed 255 characters.',
        ],

        'governorate_id' => [
            'integer' => 'The selected governorate must be valid.',
            'exists' => 'The selected governorate does not exist.',
        ],

        'page' => [
            'integer' => 'The page number must be an integer.',
            'min' => 'The page number must be at least 1.',
        ],
    ],

    'outdoor' => [
        'search' => [
            'string' => 'The search value must be a string.',
            'max' => 'The search value must not exceed 255 characters.',
        ],

        'status' => [
            'enum' => 'The selected outdoor asset status is invalid.',
        ],

        'governorate_id' => [
            'integer' => 'The selected governorate must be valid.',
            'exists' => 'The selected governorate does not exist.',
        ],

        'page' => [
            'integer' => 'The page number must be an integer.',
            'min' => 'The page number must be at least 1.',
        ],
    ],

    'customer' => [
        'page' => [
            'integer' => 'The page number must be an integer.',
            'min' => 'The page number must be at least 1.',
        ],

        'search' => [
            'string' => 'The search value must be a string.',
            'max' => 'The search value must not exceed 255 characters.',
        ],

        'contract_status' => [
            'enum' => 'The selected contract status is invalid.',
        ],

        'subscription_type' => [
            'enum' => 'The selected subscription type is invalid.',
        ],
    ],

    'customer_bookings' => [
        'page' => [
            'integer' => 'The page number must be an integer.',
            'min' => 'The page number must be at least 1.',
        ],

        'from_date' => [
            'date' => 'The from date must be a valid date.',
        ],

        'to_date' => [
            'date' => 'The to date must be a valid date.',
            'after_or_equal' => 'The to date must be after or equal to the from date.',
        ],
    ],

    'map' => [
        'search' => [
            'string' => 'The search value must be a string.',
            'max' => 'The search value must not exceed 255 characters.',
        ],

        'type' => [
            'in' => 'The selected asset type is invalid.',
        ],
    ],

    'booking_id' => 'booking ID',

];