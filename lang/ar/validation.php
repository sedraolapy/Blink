<?php

return [

    'required' => 'حقل :attribute مطلوب.',
    'email' => 'يجب أن يكون :attribute بريدًا إلكترونيًا صالحًا.',
    'in' => 'القيمة المحددة لـ :attribute غير صالحة.',
    'distinct' => 'حقل :attribute يحتوي على قيمة مكررة.',
    'boolean' => 'يجب أن تكون قيمة :attribute صحيحة أو خاطئة.',
    'present' => 'يجب أن يكون حقل :attribute موجودا.',
    'array' => 'يجب أن يكون حقل :attribute مصفوفة.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'min' => [
        'string' => 'يجب ألا يقل :attribute عن :min أحرف.',
    ],
    'max' => [
        'string' => 'يجب ألا يزيد :attribute عن :max أحرف.',
    ],
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'unique' => 'قيمة :attribute مستخدمة مسبقًا.',
    'exists' => 'القيمة المحددة في :attribute غير موجودة.',
    'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'mimes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'date_format' => 'لا يتطابق :attribute مع التنسيق :format.',


    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'customer_name' => 'اسم الزبون',
        'customer_name_ar' => 'اسم الزبون بالعربي',
        'customer_name_en' => 'اسم الزبون بالإنجليزي',
        'phone' => 'رقم الهاتف',
        'search' => 'البحث',
        'status' => 'الحالة',
        'governorate_id' => 'المحافظة',
        'period_id' => 'الفترة الإعلانية',
        'page' => 'رقم الصفحة',
        'contract_status' => 'حالة العقد',
        'subscription_type' => 'نوع الاشتراك',
        'from_date' => 'تاريخ البداية',
        'to_date' => 'تاريخ النهاية',
        'type' => 'نوع الأصل',
        'booking_id' => 'رقم الحجز',
        'period_ids' => 'الفترات',
        'customer_id' => 'الزبون',
        'advertiser_type' => 'نوع المعلن',
        'designs' => 'التصاميم',
        'design_name' => 'اسم التصميم',
        'periods' => 'الفترات',
        'items' => 'اللوحات',
        'flex_id' => 'لوحة الفليكس',
        'is_gift' => 'هدية',
        'has_dykes' => 'وجود ديكات',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'screens' => 'الشاشات',
        'screen' => 'الشاشة',
        'networks' => 'الشبكات',
        'network' => 'الشبكة',
        'slides' => 'الشرائح',
        'slide_number' => 'رقم الشريحة',
        'final_amount' => 'القيمة النهائية بعد الحسم',
        'working_year' => 'سنة العمل',
        'contract_number' => 'رقم العقد',
        'contract_images' => 'صورة عقد',
        'contract_images.*' => 'صورة العقد',
        'attachment_images' => 'صور المرفقات',
        'attachment_images.*' => 'صورة المرفق',
        'contract_image' => 'صورة العقد',
        'attachment_image' => 'صورة المرفق',
        'deleted_contract_image_ids' => 'معرّفات صور العقد المحذوفة',
        'deleted_contract_image_id' => 'معرّف صورة العقد المحذوفة',
        'deleted_attachment_image_ids' => 'معرّفات صور المرفقات المحذوفة',
        'deleted_attachment_image_id' => 'معرّف صورة المرفق المحذوفة',
    ],

        'custom' => [
            'network_id' => [
                'same_area' => 'يجب أن تكون جميع الشاشات ضمن الشبكة في نفس المنطقة.',
            ],
            'booking_id' => [
                'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
                'exists' => ':attribute المحدد غير موجود.',
            ],

            'type' => [
                'required' => 'حقل :attribute مطلوب.',
                'enum' => ':attribute المحدد غير صالح.',
            ],

            'governorate_id' => [
                'required' => 'حقل :attribute مطلوب.',
                'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
                'exists' => ':attribute المحددة غير موجودة.',
            ],

            'periods' => [
                'required' => 'حقل :attribute مطلوب.',
                'array' => 'يجب أن يكون :attribute قائمة.',
                'min' => 'يجب أن تحتوي :attribute على فترة واحدة على الأقل.',
            ],

            'period_start_date' => [
                'required' => 'حقل :attribute مطلوب.',
                'date' => 'يجب أن يكون :attribute تاريخًا صالحًا.',
            ],

            'period_end_date' => [
                'required' => 'حقل :attribute مطلوب.',
                'date' => 'يجب أن يكون :attribute تاريخًا صالحًا.',
                'after_or_equal' => 'يجب أن يكون :attribute بعد أو مساويًا لتاريخ البداية.',
            ],

            'search' => [
                'string' => 'يجب أن يكون :attribute نصًا.',
                'max' => 'يجب ألا يتجاوز :attribute 255 محرفًا.',
            ],

            'start_date' => [
                'after_or_equal' => 'يجب أن يكون :attribute ضمن سنة العمل المحددة.',
                'before_or_equal' => 'يجب أن يكون :attribute ضمن سنة العمل المحددة.',
            ],

            'end_date' => [
                'after_or_equal' => 'يجب أن يكون :attribute بعد أو مساويًا لتاريخ البداية.',
                'before_or_equal' => 'يجب أن يكون :attribute ضمن سنة العمل المحددة.',
            ],

            'contract_images' => [
                'min' => 'يجب رفع :attribute واحدة على الأقل.',
            ],

            'external_type_already_exists' => 'هذا النوع من الحجز الخارجي موجود مسبقاً ضمن الحجز.',
            'design_name_not_found' =>  'التصميم المحدد غير موجود ضمن قائمة التصاميم.',
            'external_asset_already_booked' => 'إحدى اللوحات الخارجية المحددة محجوزة مسبقًا خلال الفترة المحددة.',
            'external_type_periods_overlap' => 'لا يمكن أن تتداخل فترات نفس نوع الحجز الخارجي مع بعضها.',
            'external_periods_outside_working_year' => 'يجب أن تكون تواريخ جميع فترات الحجز الخارجي ضمن سنة العمل المحددة (:year).',
        ],

        'electronic' => [
            'search' => [
                'string' => 'يجب أن تكون قيمة البحث نصاً.',
                'max' => 'يجب ألا يتجاوز البحث 255 محرفاً.',
            ],

            'governorate_id' => [
                'integer' => 'يجب أن تكون المحافظة المحددة صحيحة.',
                'exists' => 'المحافظة المحددة غير موجودة.',
            ],

            'page' => [
                'integer' => 'يجب أن يكون رقم الصفحة رقماً صحيحاً.',
                'min' => 'يجب أن يكون رقم الصفحة 1 على الأقل.',
            ],
        ],

        'outdoor' => [
            'search' => [
                'string' => 'يجب أن تكون قيمة البحث نصاً.',
                'max' => 'يجب ألا يتجاوز البحث 255 محرفاً.',
            ],

            'status' => [
                'enum' => 'حالة الأصل الخارجي المحددة غير صحيحة.',
            ],

            'governorate_id' => [
                'integer' => 'يجب أن تكون المحافظة المحددة صحيحة.',
                'exists' => 'المحافظة المحددة غير موجودة.',
            ],

            'page' => [
                'integer' => 'يجب أن يكون رقم الصفحة رقماً صحيحاً.',
                'min' => 'يجب أن يكون رقم الصفحة 1 على الأقل.',
            ],
        ],

        'customer' => [
            'page' => [
                'integer' => 'يجب أن يكون رقم الصفحة رقمًا صحيحًا.',
                'min' => 'يجب أن يكون رقم الصفحة 1 على الأقل.',
            ],

            'search' => [
                'string' => 'يجب أن تكون قيمة البحث نصًا.',
                'max' => 'يجب ألا تتجاوز قيمة البحث 255 محرفًا.',
            ],

            'contract_status' => [
                'enum' => 'حالة العقد المحددة غير صالحة.',
            ],

            'subscription_type' => [
                'enum' => 'نوع الاشتراك المحدد غير صالح.',
            ],
        ],

        'customer_bookings' => [
            'page' => [
                'integer' => 'يجب أن يكون رقم الصفحة رقمًا صحيحًا.',
                'min' => 'يجب أن يكون رقم الصفحة 1 على الأقل.',
            ],

            'from_date' => [
                'date' => 'يجب أن يكون تاريخ البداية تاريخًا صالحًا.',
            ],

            'to_date' => [
                'date' => 'يجب أن يكون تاريخ النهاية تاريخًا صالحًا.',
                'after_or_equal' => 'يجب أن يكون تاريخ النهاية بعد أو مساويًا لتاريخ البداية.',
            ],
        ],

        'map' => [
            'search' => [
                'string' => 'يجب أن تكون قيمة البحث نصًا.',
                'max' => 'يجب ألا تتجاوز قيمة البحث 255 محرفًا.',
            ],

            'type' => [
                'in' => 'نوع الأصل المحدد غير صالح.',
            ],
        ],

        'booking_id' => 'رقم الحجز',

        'electronic_assets' => [
            'governorate_id' => [
                'required' => 'حقل :attribute مطلوب.',
                'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
                'exists' => ':attribute المحددة غير موجودة.',
            ],

            'search' => [
                'string' => 'يجب أن يكون :attribute نصًا.',
                'max' => 'يجب ألا يتجاوز :attribute 255 محرفًا.',
            ],
        ],

        'electronic_booking' => [
            'advertiser_type' => [
                'required_without' =>
                    'حقل :attribute مطلوب عند إنشاء حجز جديد.',
            ],

            'periods' => [
                'required' =>
                    'يجب إضافة فترة حجز واحدة على الأقل.',

                'min' =>
                    'يجب إضافة فترة حجز واحدة على الأقل.',

                'items_required' =>
                    'يجب إضافة شاشة مستقلة أو شاشة ضمن شبكة واحدة على الأقل ضمن الفترة.',

                'overlap' =>
                    'لا يمكن حجز نفس الشاشة ضمن فترات متداخلة في نفس الطلب.',

                'outside_working_year' => 'يجب أن تكون تواريخ جميع الفترات ضمن سنة العمل المحددة (:year).',
            ],

            'end_date' => [
                'after_or_equal' =>
                    'يجب أن يكون :attribute بعد أو مساويًا لتاريخ البداية.',
            ],

            'slides' => [
                'min' =>
                    'يجب إضافة شريحة واحدة على الأقل.',
                'duplicate' =>
                    'لا يمكن تكرار رقم الشريحة لنفس الشاشة.',
            ],

            'network_screens' => [
                'min' =>
                    'يجب إضافة شاشة واحدة على الأقل ضمن الشبكة.',
            ],

            'designs' => [
                'min' => 'يجب إضافة تصميم واحد على الأقل.',
            ],

            'design_name' => [
                'in' =>
                    'التصميم المحدد غير موجود ضمن قائمة التصاميم.',
                'required' => 'يجب اختيار تصميم لكل شريحة.',
            ],

            'customer_id' => [
                'booking_mismatch' =>
                    'الزبون المحدد لا يطابق الزبون المرتبط بالحجز.',
            ],

            'booking_id' => [
                'already_exists' =>
                    'يوجد حجز شاشات إلكترونية مرتبط بهذا الحجز مسبقًا.',
            ],


            'screens' => [
                'duplicate' =>
                    'لا يمكن تكرار نفس الشاشة ضمن نفس الفترة.',

                'not_independent' =>
                    'الشاشة المحددة ليست شاشة مستقلة.',
            ],

            'networks' => [
                'screen_mismatch' =>
                    'إحدى الشاشات المحددة لا تتبع للشبكة المحددة.',
                'all_screens_required' =>
                    'يجب اختيار جميع الشاشات التابعة للشبكة المحددة.',
            ],

            'availability' => [
                'conflict' =>
                    'إحدى الشاشات المحددة محجوزة خلال الفترة المطلوبة.',
            ],
        ],

        'contract' => [
            'permissions' => [
                'create_denied' => 'ليس لديك صلاحية لرفع العقود.',
            ],
            'start_date' => [
                'within_working_year' =>
                    'يجب أن يكون تاريخ البداية ضمن سنة العمل المحددة.',
            ],

            'end_date' => [
                'after_or_equal' =>
                    'يجب أن يكون تاريخ النهاية بعد أو مساوياً لتاريخ البداية.',

                'within_working_year' =>
                    'يجب أن يكون تاريخ النهاية ضمن سنة العمل المحددة.',
            ],

            'contract_images' => [
                'min' =>
                    'يجب رفع صورة عقد واحدة على الأقل.',
            ],

        ],

];