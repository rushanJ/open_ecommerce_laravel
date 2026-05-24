<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(SeoService $seo): Response
    {
        $body = $seo->robotsContent();

        return response($body, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
