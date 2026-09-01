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

    'attributes' => [
        'search' => 'search',
        'governorate_id' => 'governorate',
        'page' => 'page',
    ],
];