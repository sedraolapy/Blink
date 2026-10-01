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
        'booking_id' => 'booking ID',
        'period_ids' => 'periods',
        'customer_id' => 'customer',
        'advertiser_type' => 'advertiser type',
        'designs' => 'designs',
        'design_name' => 'design name',
        'periods' => 'periods',
        'items' => 'billboards',
        'flex_id' => 'flex billboard',
        'is_gift' => 'gift',
        'has_dykes' => 'dykes',
        'start_date' => 'start date',
        'end_date' => 'end date',
        'screens' => 'screens',
        'screen' => 'screen',
        'networks' => 'networks',
        'network' => 'network',
        'slides' => 'slides',
        'slide_number' => 'slide number',
    ],

    'custom' => [
        'network_id' => [
            'same_area' => 'All screens within the network must belong to the same area.',
            ],

            'booking_id' => [
                'integer' => 'The :attribute must be an integer.',
                'exists' => 'The selected :attribute does not exist.',
            ],

            'type' => [
                'required' => 'The :attribute field is required.',
                'enum' => 'The selected :attribute is invalid.',
            ],

            'governorate_id' => [
                'required' => 'The :attribute field is required.',
                'integer' => 'The :attribute must be an integer.',
                'exists' => 'The selected :attribute does not exist.',
            ],

            'periods' => [
                'required' => 'The :attribute field is required.',
                'array' => 'The :attribute must be an array.',
                'min' => 'The :attribute must contain at least one period.',
            ],

            'period_start_date' => [
                'required' => 'The :attribute field is required.',
                'date' => 'The :attribute must be a valid date.',
            ],

            'period_end_date' => [
                'required' => 'The :attribute field is required.',
                'date' => 'The :attribute must be a valid date.',
                'after_or_equal' => 'The :attribute must be after or equal to the start date.',
            ],

            'search' => [
                'string' => 'The :attribute must be a string.',
                'max' => 'The :attribute may not be greater than 255 characters.',
            ],

            'external_type_already_exists' => 'This outdoor booking type already exists for this booking.',
            'design_name_not_found' => 'The selected design does not exist in the designs list.',
            'external_asset_already_booked' => 'One or more selected external assets are already booked for the selected period.',
            'external_type_periods_overlap' => 'Periods for the same external booking type must not overlap.',
            'external_periods_outside_working_year' => 'All outdoor booking period dates must be within the selected working year (:year).',
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

    'electronic_assets' => [
        'governorate_id' => [
            'required' => 'The :attribute field is required.',
            'integer' => 'The :attribute must be an integer.',
            'exists' => 'The selected :attribute does not exist.',
        ],

        'search' => [
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than 255 characters.',
        ],
    ],

    'electronic_booking' => [
        'advertiser_type' => [
            'required_without' =>
                'The :attribute field is required when creating a new booking.',
        ],

        'periods' => [
            'required' =>
                'At least one booking period is required.',

            'min' =>
                'At least one booking period is required.',

            'items_required' =>
                'At least one independent screen or network screen is required within the period.',

            'overlap' =>
                'The same screen cannot be booked in overlapping periods within the same request.',

            'outside_working_year' => 'All period dates must be within the selected working year (:year).',
        ],

        'end_date' => [
            'after_or_equal' =>
                'The :attribute must be after or equal to the start date.',
        ],

        'slides' => [
            'min' =>
                'At least one slide is required.',
            'duplicate' =>
                'The slide number cannot be repeated for the same screen.',
        ],

        'network_screens' => [
            'min' =>
                'At least one screen is required within the network.',
        ],

        'designs' => [
            'min' => 'At least one design must be added.',
        ],

        'design_name' => [
            'in' =>
                'The selected design does not exist in the designs list.',
            'required' => 'A design must be selected for each slide.',
        ],

        'customer_id' => [
            'booking_mismatch' =>
                'The selected customer does not match the customer associated with the booking.',
        ],

        'booking_id' => [
            'already_exists' =>
                'An electronic booking already exists for this booking.',
        ],


        'screens' => [
            'duplicate' =>
                'The same screen cannot be repeated within the same period.',

            'not_independent' =>
                'The selected screen is not an independent screen.',
        ],

        'networks' => [
            'screen_mismatch' =>
                'One of the selected screens does not belong to the selected network.',
            'all_screens_required' =>
                'All screens belonging to the selected network must be included.',
        ],

        'availability' => [
            'conflict' =>
                'One of the selected screens is already booked during the requested period.',
        ],
    ],



];