<?php

namespace App\Services\Orders\Outdoor;

use Spatie\Browsershot\Browsershot;

class OutdoorOrderPdfService
{
    public function generate(
        array $data,
        array $selection
    ): string {
        $order = [
            'outdoor_type' => $data['outdoor_type'],
            'customer' => $selection['customer'],
            'period' => $selection['period'],

            'selected_items' => $selection['selected_items'],

            'issue_date' => $data['issue_date'],
            'recipient' => $data['recipient'],
            'execution_deadline' => $data['execution_deadline'],
            'notes' => $data['notes'] ?? null,
        ];

        $html = view('orders.outdoor.pdf', [
            'order' => $order,
        ])->render();

        return Browsershot::html($html)
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->disableJavascript()
            ->timeout(60)
            ->pdf();
    }
}
