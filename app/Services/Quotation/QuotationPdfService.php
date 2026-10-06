<?php

namespace App\Services\Quotation;

use Spatie\Browsershot\Browsershot;

class QuotationPdfService
{
    public function generate(string $html): string
    {
        return Browsershot::html($html)
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->disableJavascript()
            ->waitUntilNetworkIdle()
            ->timeout(60)
            ->pdf();
    }
}