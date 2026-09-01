<?php

return [

    'required' => 'حقل :attribute مطلوب.',
    'email' => 'يجب أن يكون :attribute بريدًا إلكترونيًا صالحًا.',
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
    ],

        'custom' => [
            'network_id' => [
                'same_area' => 'يجب أن تكون جميع الشاشات ضمن الشبكة في نفس المنطقة.',
            ],
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

        'attributes' => [
            'search' => 'البحث',
            'governorate_id' => 'المحافظة',
            'page' => 'الصفحة',
        ],

];