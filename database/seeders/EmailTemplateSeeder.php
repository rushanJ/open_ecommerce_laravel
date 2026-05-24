<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'order_created',
                'name' => 'Order confirmation (customer)',
                'subject' => 'Order {{ order_number }} received',
                'body_html' => <<<'HTML'
<p>Hi {{ customer_name }},</p>
<p>Thanks for shopping with <strong>{{ store_name }}</strong>.</p>
<p>We have received your order <strong>{{ order_number }}</strong>.</p>
<p><strong>Total:</strong> {{ grand_total }} {{ currency_code }}</p>
<p>You will receive another email when your payment is confirmed or if your order status changes.</p>
<p>— {{ store_name }}</p>
HTML,
                'body_text' => "Hi {{ customer_name }},\n\nWe received order {{ order_number }}. Total: {{ grand_total }} {{ currency_code }}.\n\n— {{ store_name }}",
                'variables' => ['order_number', 'customer_name', 'store_name', 'grand_total', 'currency_code', 'order_status'],
                'status' => 'active',
            ],
            [
                'code' => 'payment_success',
                'name' => 'Payment confirmed (customer)',
                'subject' => 'Payment confirmed for order {{ order_number }}',
                'body_html' => <<<'HTML'
<p>Hi {{ customer_name }},</p>
<p>Your payment for order <strong>{{ order_number }}</strong> was successful.</p>
<p><strong>Amount:</strong> {{ payment_amount }} {{ currency_code }}</p>
<p><strong>Reference:</strong> {{ payment_reference }}</p>
<p>Thank you — {{ store_name }}</p>
HTML,
                'body_text' => "Payment confirmed for order {{ order_number }}. Amount {{ payment_amount }} {{ currency_code }}. Ref: {{ payment_reference }}.\n\n— {{ store_name }}",
                'variables' => ['order_number', 'customer_name', 'store_name', 'grand_total', 'currency_code', 'payment_amount', 'payment_reference'],
                'status' => 'active',
            ],
            [
                'code' => 'order_status_changed',
                'name' => 'Order status update (customer)',
                'subject' => 'Order {{ order_number }} status updated to {{ order_status }}',
                'body_html' => <<<'HTML'
<p>Hi {{ customer_name }},</p>
<p>Your order <strong>{{ order_number }}</strong> status is now <strong>{{ order_status }}</strong>.</p>
<p>If you have questions, reply to this email or contact {{ store_name }}.</p>
HTML,
                'body_text' => "Order {{ order_number }} status is now {{ order_status }}.\n\n— {{ store_name }}",
                'variables' => ['order_number', 'customer_name', 'store_name', 'order_status', 'old_status', 'new_status', 'grand_total', 'currency_code'],
                'status' => 'active',
            ],
            [
                'code' => 'admin_new_order',
                'name' => 'New order (admin)',
                'subject' => 'New order received: {{ order_number }}',
                'body_html' => <<<'HTML'
<p>A new order was placed.</p>
<p><strong>Order:</strong> {{ order_number }}</p>
<p><strong>Customer:</strong> {{ customer_name }}</p>
<p><strong>Total:</strong> {{ grand_total }} {{ currency_code }}</p>
<p>Open the admin panel to review and fulfill this order.</p>
HTML,
                'body_text' => "New order {{ order_number }} from {{ customer_name }}. Total {{ grand_total }} {{ currency_code }}.",
                'variables' => ['order_number', 'customer_name', 'store_name', 'grand_total', 'currency_code', 'order_status'],
                'status' => 'active',
            ],
        ];

        foreach ($templates as $row) {
            EmailTemplate::query()->firstOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'subject' => $row['subject'],
                    'body_html' => $row['body_html'],
                    'body_text' => $row['body_text'],
                    'variables' => $row['variables'],
                    'status' => $row['status'],
                ],
            );
        }
    }
}
