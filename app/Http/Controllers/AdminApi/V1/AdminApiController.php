<?php

namespace App\Http\Controllers\AdminApi\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

abstract class AdminApiController extends Controller
{
    use ApiResponse;
}
