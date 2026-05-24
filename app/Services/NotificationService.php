<?php

namespace App\Services;

use App\Mail\TemplateMail;
use App\Models\AdminUser;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(
        protected TemplateRendererService $renderer,
        protected SettingService $settings,
        protected StoreService $stores,
    ) {}

    public function notifyAdmin(string $type, string $title, string $message, array $data = [], ?int $adminId = null): void
    {
        try {
            Notification::query()->create([
                'recipient_type' => 'admin',
                'recipient_id' => $adminId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to persist admin notification', [
                'type' => $type,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function notifyCustomer(Customer $customer, string $type, string $title, string $message, array $data = []): void
    {
        try {
            Notification::query()->create([
                'recipient_type' => 'customer',
                'recipient_id' => $customer->getKey(),
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to persist customer notification', [
                'type' => $type,
                'customer_id' => $customer->getKey(),
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function notifyOrderCreated(Order $order): void
    {
        $order->loadMissing(['customer', 'billingAddress']);

        $ctx = $this->orderTemplateData($order);

        try {
            if ($order->customer_id !== null && $order->customer !== null) {
                $this->notifyCustomer(
                    $order->customer,
                    'order.created',
                    __('customer.notification_order_created_title', ['number' => $order->order_number]),
                    __('customer.notification_order_created_message', ['number' => $order->order_number]),
                    ['order_id' => $order->getKey()]
                );
            }

            $this->notifyAdmin(
                'admin.new_order',
                __('admin.notification_new_order_title', ['number' => $order->order_number]),
                __('admin.notification_new_order_message', ['number' => $order->order_number]),
                ['order_id' => $order->getKey()],
                null,
            );
        } catch (\Throwable $e) {
            Log::error('notifyOrderCreated persistence failed', ['exception' => $e->getMessage()]);
        }

        $email = trim((string) $order->customer_email);
        if ($email !== '') {
            $this->sendTemplateEmailSafely($email, 'order_created', $ctx);
        }

        $this->sendAdminBroadcastTemplateSafely('admin_new_order', $ctx);
    }

    public function notifyPaymentSuccess(Order $order, Payment $payment): void
    {
        $order->loadMissing(['customer', 'billingAddress']);
        $ctx = array_merge($this->orderTemplateData($order), [
            'payment_amount' => number_format((float) $payment->amount, 2),
            'payment_reference' => (string) $payment->payment_reference,
        ]);

        try {
            if ($order->customer_id !== null && $order->customer !== null) {
                $this->notifyCustomer(
                    $order->customer,
                    'order.payment_success',
                    __('customer.notification_payment_success_title', ['number' => $order->order_number]),
                    __('customer.notification_payment_success_message', ['number' => $order->order_number]),
                    ['order_id' => $order->getKey(), 'payment_id' => $payment->getKey()]
                );
            }
        } catch (\Throwable $e) {
            Log::error('notifyPaymentSuccess persistence failed', ['exception' => $e->getMessage()]);
        }

        $email = trim((string) $order->customer_email);
        if ($email !== '') {
            $this->sendTemplateEmailSafely($email, 'payment_success', $ctx);
        }
    }

    public function notifyOrderStatusChanged(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $order->loadMissing(['customer', 'billingAddress']);
        $ctx = array_merge($this->orderTemplateData($order), [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'order_status' => $newStatus,
        ]);

        try {
            if ($order->customer_id !== null && $order->customer !== null) {
                $this->notifyCustomer(
                    $order->customer,
                    'order.status_changed',
                    __('customer.notification_order_status_title', ['number' => $order->order_number]),
                    __('customer.notification_order_status_message', [
                        'number' => $order->order_number,
                        'status' => $newStatus,
                    ]),
                    ['order_id' => $order->getKey(), 'old_status' => $oldStatus, 'new_status' => $newStatus]
                );
            }
        } catch (\Throwable $e) {
            Log::error('notifyOrderStatusChanged persistence failed', ['exception' => $e->getMessage()]);
        }

        $email = trim((string) $order->customer_email);
        if ($email !== '') {
            $this->sendTemplateEmailSafely($email, 'order_status_changed', $ctx);
        }
    }

    /**
     * @return array<string, string>
     */
    private function orderTemplateData(Order $order): array
    {
        $order->loadMissing(['customer', 'billingAddress']);

        $name = '';
        if ($order->customer !== null) {
            $name = trim(($order->customer->first_name ?? '').' '.($order->customer->last_name ?? ''));
        }
        if ($name === '' && $order->billingAddress !== null) {
            $name = trim(($order->billingAddress->first_name ?? '').' '.($order->billingAddress->last_name ?? ''));
        }
        if ($name === '') {
            $name = $order->customer_email ?: __('customer.guest_customer');
        }

        $storeName = config('open_ecommerce_laravel.store.name', config('app.name'));
        try {
            $storeName = $this->stores->currentStore()->name ?: $storeName;
        } catch (\Throwable) {
            // ignore
        }

        return [
            'order_number' => $order->order_number,
            'customer_name' => $name,
            'store_name' => $storeName,
            'grand_total' => number_format((float) $order->grand_total, 2),
            'currency_code' => (string) $order->currency_code,
            'order_status' => $order->status,
        ];
    }

    /**
     * @param  array<string, string>  $data
     */
    private function sendTemplateEmailSafely(string $toEmail, string $templateCode, array $data): void
    {
        try {
            $template = EmailTemplate::query()->where('code', $templateCode)->active()->first();
            if ($template === null) {
                return;
            }

            $subject = $this->renderer->render($template->subject, $data);
            $html = $this->renderer->render($template->body_html, $data);
            $text = $template->body_text
                ? $this->renderer->render($template->body_text, $data)
                : strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));

            $this->applyMailSettingsToConfig();

            Mail::to($toEmail)->send(new TemplateMail($toEmail, $subject, $html, $text));
        } catch (\Throwable $e) {
            Log::error('Transactional email failed', [
                'to' => $toEmail,
                'template' => $templateCode,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, string>  $data
     */
    private function sendAdminBroadcastTemplateSafely(string $templateCode, array $data): void
    {
        try {
            $template = EmailTemplate::query()->where('code', $templateCode)->active()->first();
            if ($template === null) {
                return;
            }

            $subject = $this->renderer->render($template->subject, $data);
            $html = $this->renderer->render($template->body_html, $data);
            $text = $template->body_text
                ? $this->renderer->render($template->body_text, $data)
                : strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));

            $this->applyMailSettingsToConfig();

            $emails = AdminUser::query()
                ->where('status', 'active')
                ->whereNotNull('email')
                ->pluck('email')
                ->map(fn ($e) => trim((string) $e))
                ->filter(fn ($e) => $e !== '')
                ->unique()
                ->values();

            foreach ($emails as $adminEmail) {
                try {
                    Mail::to($adminEmail)->send(new TemplateMail($adminEmail, $subject, $html, $text));
                } catch (\Throwable $e) {
                    Log::error('Admin transactional email failed', [
                        'to' => $adminEmail,
                        'template' => $templateCode,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Admin broadcast email batch failed', [
                'template' => $templateCode,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function applyMailSettingsToConfig(): void
    {
        $fromAddress = $this->settings->get('mail.from_address');
        if (is_string($fromAddress) && $fromAddress !== '') {
            Config::set('mail.from.address', $fromAddress);
            $name = $this->settings->get('mail.from_name');
            Config::set('mail.from.name', is_string($name) ? $name : '');
        }

        $host = $this->settings->get('mail.smtp_host');
        if (! is_string($host) || $host === '') {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);

        $port = $this->settings->get('mail.smtp_port');
        if ($port !== null) {
            Config::set('mail.mailers.smtp.port', (int) $port);
        }

        $enc = $this->settings->get('mail.smtp_encryption');
        if (is_string($enc) && in_array($enc, ['tls', 'ssl'], true)) {
            Config::set('mail.mailers.smtp.encryption', $enc);
        } else {
            Config::set('mail.mailers.smtp.encryption', null);
        }

        $user = $this->settings->get('mail.smtp_username');
        Config::set('mail.mailers.smtp.username', is_string($user) ? $user : '');

        $pass = $this->settings->get('mail.smtp_password');
        Config::set('mail.mailers.smtp.password', is_string($pass) ? $pass : '');
    }
}
