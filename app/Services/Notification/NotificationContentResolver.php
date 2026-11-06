<?php

namespace App\Services\Notification;

use App\Enums\ExternalAssetTypeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class NotificationContentResolver
{
    public function resolve(DatabaseNotification $notification,Collection $customers): array
    {
        $data = $notification->data ?? [];
        $context = $data['context'] ?? [];
        $type = $data['type'] ?? $notification->type;
        $parameters = $this->parameters($type,$context,$customers);
        $key = "notifications.types.{$type}";


        return [
            'title' => $this->translate(
                "{$key}.title",
                $parameters,
                $data['title'] ?? ''
            ),

            'message' => $this->translate(
                "{$key}.message",
                $parameters,
                $data['message'] ?? ''
            ),

            'context' => $this->resolveContext(
                $type,
                $context,
                $parameters['customer']
            ),
        ];

    }


    private function resolveContext(string $type,array $context,string $customerName): array|object
    {
        if ($type === 'flex_orders_reminder') {
            $context['name_customer'] = $customerName;
        }

        if (in_array($type, [
            'contract_start_reminder',
            'contract_started',
            'contract_end_reminder',
            'contract_ended',
            'electronic_orders_reminder',
            'electronic_period_started',
            'electronic_period_ended',
        ], true)) {
            $context['customer_name'] = $customerName;
        }

        if (in_array($type, [
            'outdoor_orders_reminder',
            'outdoor_period_started',
            'outdoor_period_ended',
        ], true)) {
            $context['customer_name'] = $customerName;

            $context['outdoor_type_name'] =
                ExternalAssetTypeEnum::tryFrom($context['outdoor_type'] ?? '')
                    ?->label()
                ?? ($context['outdoor_type'] ?? '');
        }

        if ($type === 'monthly_institution_report_reminder') {
            $context['month_name'] = $this->monthName($context);
        }

        return $context ?: (object) [];
    }


    private function translate(string $key,array $parameters,string $fallback)
    {
        return Lang::has($key)
            ? __($key, $parameters)
            : $fallback;
    }

    private function parameters(string $type,array $context,Collection $customers)
    {
        return [
            'customer' => $this->customerName($context,$customers),
            'period' => $context['period_number'] ?? '',
            'date' => $this->formattedDate($type,$context),
            'outdoor_type' => $this->outdoorType($context),
            'month' => $this->monthName($context),
        ];
    }

    private function customerName(array $context,Collection $customers)
    {
        $customer = $customers->get($context['customer_id'] ?? null);

        return $customer?->name
            ?? ($context['customer_name'] ?? '');
    }

    private function formattedDate(string $type,array $context)
    {
        $date = match ($type) {
            'contract_start_reminder' => $context['start_date'] ?? null,
            'contract_end_reminder' => $context['end_date'] ?? null,
            default => null,
        };

        return $date
            ? CarbonImmutable::parse($date)->format('d/m/Y')
            : '';
    }

    private function outdoorType(array $context): string
    {
        return ExternalAssetTypeEnum::tryFrom(
            $context['outdoor_type'] ?? ''
        )?->label() ?? '';
    }

    private function monthName(array $context): string
    {
        $month = (int) ($context['month'] ?? 0);

        if ($month < 1 || $month > 12) {
            return '';
        }

        if (app()->isLocale('ar')) {
            return [
                1 => 'كانون الثاني',
                2 => 'شباط',
                3 => 'آذار',
                4 => 'نيسان',
                5 => 'أيار',
                6 => 'حزيران',
                7 => 'تموز',
                8 => 'آب',
                9 => 'أيلول',
                10 => 'تشرين الأول',
                11 => 'تشرين الثاني',
                12 => 'كانون الأول',
            ][$month];
        }

        return CarbonImmutable::create(
            (int) ($context['year'] ?? now()->year),
            $month,
            1
        )->locale('en')->translatedFormat('F');
    }
}
