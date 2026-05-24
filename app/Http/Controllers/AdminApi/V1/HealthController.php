<?php

namespace App\Http\Controllers\AdminApi\V1;

use Illuminate\Http\JsonResponse;

class HealthController extends AdminApiController
{
    public function __invoke(): JsonResponse
    {
        return $this->success([
            'status' => 'ok',
            'version' => '1',
        ]);
    }
}
