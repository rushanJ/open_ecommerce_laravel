<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreEmailTemplateRequest;
use App\Http\Requests\Admin\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends BaseAdminController
{
    public function index(Request $request): View
    {
        $query = EmailTemplate::query()->orderBy('code');

        if ($request->filled('q')) {
            $q = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($q): void {
                $w->where('code', 'like', $q)
                    ->orWhere('name', 'like', $q)
                    ->orWhere('subject', 'like', $q);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $templates = $query->paginate(20)->withQueryString();

        return view('admin.email_templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.email_templates.create', [
            'template' => new EmailTemplate(['status' => 'active']),
        ]);
    }

    public function store(StoreEmailTemplateRequest $request): RedirectResponse
    {
        $template = EmailTemplate::query()->create($this->payloadFromRequest($request));
        $this->logAdminActivity('content', 'create_email_template', $template, [], $template->only(['code', 'name', 'status']));

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', __('admin.email_template_created'));
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        return view('admin.email_templates.edit', ['template' => $emailTemplate]);
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $before = $emailTemplate->only(['code', 'name', 'status', 'subject']);
        $emailTemplate->update($this->payloadFromRequest($request));
        $updated = $emailTemplate->fresh();
        $this->logAdminActivity('content', 'update_email_template', $updated, $before, $updated?->only(['code', 'name', 'status', 'subject']) ?? []);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', __('admin.email_template_updated'));
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        $code = (string) $emailTemplate->code;
        if (Str::startsWith($code, 'order_') || Str::startsWith($code, 'payment_')) {
            return back()->with('error', __('admin.email_template_delete_blocked'));
        }

        $snapshot = $emailTemplate->only(['id', 'code']);
        $emailTemplate->delete();
        $this->logAdminActivity('content', 'delete_email_template', null, $snapshot, []);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', __('admin.email_template_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(StoreEmailTemplateRequest|UpdateEmailTemplateRequest $request): array
    {
        $data = $request->validated();
        $vars = $data['variables'] ?? null;
        if (is_array($vars)) {
            $clean = array_values(array_filter($vars, fn ($v) => is_string($v) && trim($v) !== ''));
            $data['variables'] = $clean === [] ? null : $clean;
        } else {
            $data['variables'] = null;
        }

        return $data;
    }
}
