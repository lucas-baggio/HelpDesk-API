<?php

namespace App\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Http\Concerns\RespondsWithApiEnvelope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class ApiController extends Controller
{
    use AuthorizesRequests, RespondsWithApiEnvelope;
}
