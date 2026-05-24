<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateGeneralSettingsRequest;
use App\Http\Requests\Admin\UpdateMailSettingsRequest;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Http\Requests\Admin\UpdateStoreSettingsRequest;
use App\Models\AdminUser;
use App\Services\MediaService;
use App\Services\SettingService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends BaseAdminController
{
    public function __construct(
        protected SettingService $settings,
        protected StoreService $stores,
        protected MediaService $media,
    ) {}

    public function store(): View
    {
        $store = $this->stores->currentStore();

        return view('admin.settings.store', [
            'store' => $store,
        ]);
    }

    public function updateStore(UpdateStoreSettingsRequest $request): RedirectResponse
    {
        /** @var AdminUser|null $admin */
        $admin = $request->user('admin');

        $data = $request->safe()->except(['logo_file', 'favicon_file']);

        if ($request->hasFile('logo_file')) {
            $media = $this->media->uploadImage($request->file('logo_file'), $admin, 'store');
            $data['logo_path'] = $media->path;
        }

        if ($request->hasFile('favicon_file')) {
            $media = $this->media->uploadImage($request->file('favicon_file'), $admin, 'store');
            $data['favicon_path'] = $media->path;
        }

        $store = $this->stores->updateStore($data);

        $this->logAdminActivity('settings', 'update_store', $store, [], ['store_id' => $store->getKey(), 'name' => $store->name]);

        $this->settings->set('store.name', $store->name, 'string', 'store', true);
        if ($store->domain) {
            $this->settings->set('store.domain', $store->domain, 'string', 'store', false);
        }
        $this->settings->set('store.email', $store->email, 'string', 'store', false);

        return $this->backWithSuccess(__('admin.settings_updated'));
    }

    public function general(): View
    {
        $store = $this->stores->currentStore();
        $maintenanceMode = (bool) $this->settings->get('general.maintenance_mode', false);

        return view('admin.settings.general', [
            'store' => $store,
            'maintenanceMode' => $maintenanceMode,
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $this->stores->updateStore([
            'currency_code' => $request->validated('currency_code'),
            'timezone' => $request->validated('timezone'),
        ]);

        $this->settings->set(
            'general.maintenance_mode',
            $request->boolean('maintenance_mode'),
            'boolean',
            'general',
            false,
        );

        $store = $this->stores->currentStore();
        $this->settings->set('store.currency', $store->currency_code, 'string', 'store', true);
        $this->settings->set('store.timezone', $store->timezone, 'string', 'store', false);

        $this->logAdminActivity('settings', 'update_general', null, [], [
            'currency_code' => $store->currency_code,
            'timezone' => $store->timezone,
            'maintenance_mode' => $request->boolean('maintenance_mode'),
        ]);

        return $this->backWithSuccess(__('admin.settings_updated'));
    }

    public function payments(): View
    {
        return view('admin.settings.payments', [
            'payhereEnabled' => (bool) $this->settings->get('payhere.enabled', false),
            'payhereMode' => (string) $this->settings->get('payhere.mode', 'sandbox'),
            'payhereMerchantId' => (string) ($this->settings->get('payhere.merchant_id') ?? ''),
        ]);
    }

    public function updatePayments(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $this->settings->set('payhere.enabled', $request->boolean('payhere_enabled'), 'boolean', 'payhere', false);
        $this->settings->set('payhere.mode', $request->validated('payhere_mode'), 'string', 'payhere', false);

        $merchantId = (string) ($request->validated('payhere_merchant_id') ?? '');
        $this->settings->set('payhere.merchant_id', $merchantId, 'string', 'payhere', false);

        $secret = $request->validated('payhere_merchant_secret');
        if ($secret !== null && $secret !== '') {
            $this->settings->set('payhere.merchant_secret', $secret, 'string', 'payhere', false);
        }

        $this->logAdminActivity('settings', 'update_payments', null, [], [
            'payhere_enabled' => $request->boolean('payhere_enabled'),
            'payhere_mode' => $request->validated('payhere_mode'),
            'merchant_id_updated' => $merchantId !== '',
        ]);

        return $this->backWithSuccess(__('admin.settings_updated'));
    }

    public function mail(): View
    {
        return view('admin.settings.mail', [
            'mailFromName' => (string) ($this->settings->get('mail.from_name') ?? ''),
            'mailFromAddress' => (string) ($this->settings->get('mail.from_address') ?? ''),
            'smtpHost' => (string) ($this->settings->get('mail.smtp_host') ?? ''),
            'smtpPort' => $this->settings->get('mail.smtp_port'),
            'smtpUsername' => (string) ($this->settings->get('mail.smtp_username') ?? ''),
            'smtpEncryption' => (string) ($this->settings->get('mail.smtp_encryption') ?? ''),
        ]);
    }

    public function updateMail(UpdateMailSettingsRequest $request): RedirectResponse
    {
        $this->settings->set('mail.from_name', $request->validated('mail_from_name'), 'string', 'mail', false);
        $this->settings->set('mail.from_address', $request->validated('mail_from_address'), 'string', 'mail', false);

        $host = $request->validated('smtp_host');
        $this->settings->set('mail.smtp_host', $host ?? '', 'string', 'mail', false);

        $port = $request->validated('smtp_port');
        if ($port === null) {
            $this->settings->forget('mail.smtp_port');
        } else {
            $this->settings->set('mail.smtp_port', $port, 'integer', 'mail', false);
        }

        $user = $request->validated('smtp_username');
        $this->settings->set('mail.smtp_username', $user ?? '', 'string', 'mail', false);

        $enc = $request->validated('smtp_encryption');
        $this->settings->set('mail.smtp_encryption', $enc ?? '', 'string', 'mail', false);

        $pass = $request->validated('smtp_password');
        if ($pass !== null && $pass !== '') {
            $this->settings->set('mail.smtp_password', $pass, 'string', 'mail', false);
        }

        $this->logAdminActivity('settings', 'update_mail', null, [], [
            'from_address' => $request->validated('mail_from_address'),
            'smtp_host_set' => ($host ?? '') !== '',
            'smtp_password_updated' => $pass !== null && $pass !== '',
        ]);

        return $this->backWithSuccess(__('admin.settings_updated'));
    }

    public function seo(): View
    {
        return view('admin.settings.seo', [
            'seoTitle' => (string) ($this->settings->get('seo.default_title') ?? ''),
            'seoDescription' => (string) ($this->settings->get('seo.default_description') ?? ''),
            'seoKeywords' => (string) ($this->settings->get('seo.default_keywords') ?? ''),
        ]);
    }

    public function updateSeo(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        $this->settings->set('seo.default_title', $request->validated('seo_default_title'), 'string', 'seo', true);
        $desc = $request->validated('seo_default_description');
        $this->settings->set('seo.default_description', $desc ?? '', 'string', 'seo', true);
        $kw = $request->validated('seo_default_keywords');
        $this->settings->set('seo.default_keywords', $kw ?? '', 'string', 'seo', true);

        $this->logAdminActivity('settings', 'update_seo', null, [], ['updated' => true]);

        return $this->backWithSuccess(__('admin.settings_updated'));
    }
}
