<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MicropubService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class MicropubController extends Controller
{
    public function __construct(public MicropubService $service)
    {
        //
    }

    public function getCapabilities(Request $request): JsonResponse
    {
        return $this->service->getCapabilities($request);
    }

    public function publish(Request $request): Response
    {
        return $this->service->processRequest($request);
    }
}
