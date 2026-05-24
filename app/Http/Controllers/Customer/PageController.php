<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(Page $page): View
    {
        abort_unless($page->isPublished(), 404);

        return view('customer.pages.show', [
            'page' => $page,
        ]);
    }
}

