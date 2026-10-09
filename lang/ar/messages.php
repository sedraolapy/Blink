<?php

return [
    'validation_failed' => 'البيانات المدخلة غير صحيحة.',
    'invalid_credentials' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
    'login_success' => 'تم تسجيل الدخول بنجاح.',
    'logout_success' => 'تم تسجيل الخروج بنجاح.',
    'customers_retrieved' => 'تم جلب الزبائن بنجاح.',
    'customer_retrieved' => 'تم جلب بيانات الزبون بنجاح.',
    'customer_created' => 'تمت إضافة الزبون بنجاح.',
    'customer_updated' => 'تم تعديل بيانات الزبون بنجاح.',
    'auth_user_retrieved' => 'تم جلب بيانات المستخدم الحالي بنجاح.',
    'flex_billboards_retrieved' => 'تم جلب لوحات الفليكس بنجاح.',
    'flex_billboard_retrieved' => 'تم جلب تفاصيل لوحة الفليكس بنجاح.',
    'advertising_periods_retrieved' => 'تم جلب الفترات الإعلانية بنجاح.',
    'governorates_retrieved' => 'تم جلب المحافظات بنجاح.',
    'electronic' => [
        'index_success' => 'تم جلب الشاشات والشبكات الإلكترونية بنجاح.',
        'screen_retrieved' => 'تم جلب تفاصيل الشاشة الإلكترونية بنجاح.',
        'network_retrieved' => 'تم جلب تفاصيل شبكة الشاشات الإلكترونية بنجاح.',
    ],
        'outdoor' => [
        'index_success' => 'تم جلب الأصول الخارجية بنجاح.',
        'show_success' => 'تم جلب تفاصيل الأصل الخارجي بنجاح.',
    ],
    'bookings_success' => 'تم جلب حجوزات الزبون بنجاح.',
    'map' => [
        'index_success' => 'تم جلب بيانات الخريطة بنجاح.',
    ],
    'periods_retrieved' => 'تم جلب فترات حجز الفليكس بنجاح.',

    'flex_booking' => [
        'periods_retrieved' => 'تم جلب فترات حجز الفليكس بنجاح.',
        'available_assets_retrieved' => 'تم جلب لوحات الفليكس المتاحة بنجاح.',
        'saved' => 'تم حفظ حجز الفليكس بنجاح.',
        'customer_mismatch' => 'الزبون المرسل لا يطابق الزبون المرتبط بالحجز.',
        'already_exists' => 'يوجد حجز فليكس مرتبط بهذا الحجز مسبقاً.',
        'assets_not_available' => 'إحدى لوحات الفليكس المختارة لم تعد متاحة ضمن الفترة المحددة.',
        'updated' => 'تم تعديل حجز الفليكس بنجاح.',
        'duplicate_assets' => 'لا يمكن اختيار لوحة الفليكس نفسها أكثر من مرة ضمن نفس الفترة.',
        'show' => 'تم جلب حجز الفليكس بنجاح.',
        'assets_must_match_across_periods' => 'يجب أن تكون لوحات الفليكس نفسها في جميع الفترات المختارة.',
    ],

    'booking' => [
        'updated' => 'تم تعديل الحجز بنجاح.',
        'show' => 'تم جلب الحجز بنجاح.',
    ],

    'electronic_booking_options' => [
        'assets_success' => 'تم جلب خيارات الشاشات الإلكترونية بنجاح.',
    ],

    'electronic_booking' => [
        'store_success' => 'تم حفظ حجز الشاشات الإلكترونية بنجاح.',
        'update_success' => 'تم تعديل حجز الشاشات الإلكترونية بنجاح.',
        'show' => 'تم جلب تفاصيل الحجز الإلكتروني بنجاح.',
    ],

    'not_found' => 'العنصر المطلوب غير موجود.',
    'forbidden' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء.',

    'external_booking' => [
        'saved' => 'تم حفظ الحجز الخارجي بنجاح.',
        'available_assets_retrieved' =>'تم جلب الأصول الخارجية المتاحة بنجاح.',
        'show' => 'تم جلب تفاصيل الحجز الخارجي بنجاح.',
        'updated' => 'تم تحديث الحجز الخارجي بنجاح.',
    ],

    'working_year_required' => 'سنة العمل مطلوبة.',
    'working_year_not_available' => 'سنة العمل المحددة غير متاحة للحجز.',
    'working_year_not_initialized' => 'لم يتم تهيئة سنة العمل.',
    'working_year_read_only' => 'لا يمكن تعديل البيانات ضمن سنة عمل سابقة.',

    'quotation' => [
        'show' => 'تم جلب عرض السعر بنجاح.',
        'issue' => 'تم إصدار عرض السعر بنجاح.',
    ],

    'contract' => [
        'already_exists' => 'تم رفع عقد لهذا الحجز مسبقاً.',
        'uploaded' => 'تم رفع العقد بنجاح.',
        'show' => 'تم جلب العقد بنجاح.',
        'not_uploaded' => 'لم يتم رفع عقد بعد.',
        'updated' => 'تم تحديث العقد بنجاح.',
    ],

    'dashboard' => [
        'index_success' => 'تم جلب إحصائيات الصفحة الرئيسية بنجاح.',
        'top_requested_assets_success' => 'تم جلب الأصول الأكثر طلباً بنجاح.',
    ],

];