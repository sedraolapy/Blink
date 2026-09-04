<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case ACCESS_DASHBOARD = 'access_dashboard';

    case CREATE_FLEX_BOOKING = 'create_flex_booking';
    case CREATE_SCREEN_BOOKING = 'create_screen_booking';
    case CREATE_EXTERNAL_BOOKING = 'create_external_booking';

    case UPDATE_BOOKING = 'update_booking';
    case DELETE_BOOKING = 'delete_booking';

    case UPDATE_CUSTOMER = 'update_customer';

    case UPLOAD_CONTRACT = 'upload_contract';

    case EXPORT_QUOTATION = 'export_quotation';

    case MANAGE_ORDERS = 'manage_orders';

    case MANAGE_REPORTS = 'manage_reports';
}