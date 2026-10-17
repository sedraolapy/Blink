<?php

namespace App\Services\Orders\Electronic;

use Spatie\Browsershot\Browsershot;

class ElectronicOrderPdfService
{
    public function generate(
        array $data,
        array $selection
    ): string {
        $order = [
            'customer' => $selection['customer'],
            'period' => $selection['period'],
            'screens' => $selection['screens'],
            'networks' => $selection['networks'],
            'issue_date' => $data['issue_date'],
            'recipient' => $data['recipient'],
            'execution_deadline' => $data['execution_deadline'],
            'notes' => $data['notes'] ?? null,
        ];

        $html = view('orders.electronic.pdf', [
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
