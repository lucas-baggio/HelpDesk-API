<?php

namespace App\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Http\Concerns\RespondsWithApiEnvelope;

abstract class ApiController extends Controller
{
    use RespondsWithApiEnvelope;
}
