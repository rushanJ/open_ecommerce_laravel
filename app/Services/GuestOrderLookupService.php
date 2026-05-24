<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class GuestOrderLookupService
{
    public function findByOrderNumberAndContact(string $orderNumber, string $contact): ?Order
    {
        $contact = trim(mb_strtolower($contact));

        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->first();

        if ($order === null) {
            return null;
        }

        $email = trim(mb_strtolower((string) $order->customer_email));
        $phone = preg_replace('/\s+/', '', (string) $order->customer_phone);
        $contactNoSpaces = preg_replace('/\s+/', '', $contact);

        $matches = ($email !== '' && Str::lower($email) === $contact)
            || ($phone !== '' && $phone === $contactNoSpaces);

        if (! $matches) {
            return null;
        }

        return $order->load([
            'items.product',
            'items.variant',
            'addresses',
            'statusHistories',
            'latestPayment.paymentMethod',
        ]);
    }
}
