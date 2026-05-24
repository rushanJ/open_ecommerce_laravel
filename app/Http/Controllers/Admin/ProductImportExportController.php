<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ImportProductCsvRequest;
use App\Models\ImportJob;
use App\Services\ImportExport\ProductExportService;
use App\Services\ImportExport\ProductImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProductImportExportController extends BaseAdminController
{
    public function __construct(
        protected ProductExportService $exportService,
        protected ProductImportService $importService,
    ) {}

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->abortUnlessCan('products.export');

        $filters = $request->only(['q', 'status', 'product_type', 'brand_id', 'category_id']);

        $this->logAdminActivity('products', 'export_csv', null, [], $filters);

        return $this->exportService->exportProducts($filters);
    }

    public function importForm(): View
    {
        $this->abortUnlessCan('products.create');

        return view('admin.products.import');
    }

    public function upload(ImportProductCsvRequest $request): RedirectResponse
    {
        $this->abortUnlessCan('products.create');
        $this->abortUnlessCan('products.update');

        /** @var \App\Models\AdminUser $admin */
        $admin = $request->user('admin');

        $job = $this->importService->createJob($request->file('file'), $admin);
        $this->importService->parseAndValidate($job);
        $this->logAdminActivity('products', 'import_csv_upload', $job, [], ['filename' => $job->filename]);

        return redirect()
            ->route('admin.import-jobs.show', $job)
            ->with('success', __('admin.import_uploaded'));
    }

    public function show(ImportJob $importJob): View
    {
        $this->abortUnlessCan('products.update');

        if ($importJob->type !== 'product_import') {
            abort(404);
        }

        $previewRows = $importJob->rows()->orderBy('row_number')->limit(50)->get();

        $failedRows = $importJob->rows()
            ->whereIn('status', ['invalid', 'failed'])
            ->orderBy('row_number')
            ->limit(200)
            ->get();

        return view('admin.import_jobs.show', [
            'job' => $importJob,
            'previewRows' => $previewRows,
            'failedRows' => $failedRows,
        ]);
    }

    public function process(Request $request, ImportJob $importJob): RedirectResponse
    {
        $this->abortUnlessCan('products.update');

        if ($importJob->type !== 'product_import') {
            abort(404);
        }

        try {
            $this->importService->process($importJob->fresh());
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('admin.import-jobs.show', $importJob)
                ->with('error', $e->getMessage());
        }

        $this->logAdminActivity('products', 'import_csv_process', $importJob->fresh(), [], []);

        return redirect()
            ->route('admin.import-jobs.show', $importJob->fresh())
            ->with('success', __('admin.import_processed'));
    }
}
