<?php

namespace App\Http\Controllers;

use App\Services\Payments\PayHereService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    public function payhereNotify(Request $request, PayHereService $payHereService): Response
    {
        Log::info('payhere.notify.raw', [
            'ip' => $request->ip(),
            'content_type' => $request->header('Content-Type'),
            'body' => $request->getContent(),
        ]);

        $payHereService->handleNotify($request);

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }
}

