<?php

return [

    'roles' => [
        'super_admin' => 'المدير العام للنظام',
        'admin' => 'مدير لوحة التحكم',
        'flex_booking_officer' => 'مسؤول حجوزات الفليكس',
        'screen_booking_officer' => 'مسؤول حجوزات الشاشات الإلكترونية',
        'external_booking_officer' => 'مسؤول حجوزات الإعلانات الخارجية',
        'sales_coordinator' => 'منسق المبيعات',
        'sales_manager' => 'مدير المبيعات',
    ],

    'subscription_types' => [
        'bronze' => 'برونزي',
        'silver' => 'فضي',
        'gold' => 'ذهبي',
    ],

    'display_groups' => [
        'damascus_daraa_sweida' => 'دمشق ودرعا والسويداء',
        'others' => 'باقي المحافظات',
    ],

    'installation_days' => [
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
        'saturday' => 'السبت',
    ],

    'external_asset_types' => [
        'unipole' => 'يوني بول',
        'bridge' => 'جسر',
        'tunnel' => 'نفق',
        'mural' => 'جدارية',
        'rooftop' => 'سطحية',
    ],

    'booking_types' => [
        'internal' => 'داخلي',
        'external' => 'خارجي',
    ],

    'contract_statuses' => [
        'pending' => 'قيد الانتظار',
        'waiting_start' => 'بانتظار بداية العقد',
        'in_progress' => 'قيد التنفيذ',
        'finished' => 'منتهي',
    ],

    'flex_statuses' => [
        'available' => 'متاحة',
        'unconfirmed' => 'غير مؤكدة',
        'booked' => 'محجوزة',
    ],

    'flex_booking_item_statuses' => [
        'unconfirmed' => 'غير مؤكدة',
        'booked' => 'محجوزة',
    ],

];