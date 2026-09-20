<?php
namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Str;

class QPayService
{
    public function createInvoice(Order $order): array
    {
        if (!config('services.qpay.enabled')) return ['invoice_id' => 'DEMO-'.$order->order_number, 'qr_text' => 'QPay demo invoice', 'demo' => true];
        throw new \RuntimeException('QPay production credentials/API тохируулаагүй байна.');
    }
    public function checkPayment(string $invoiceId): array { return ['invoice_id' => $invoiceId, 'paid' => false]; }
    public function cancelInvoice(string $invoiceId): bool { return (bool) Str::length($invoiceId); }
}