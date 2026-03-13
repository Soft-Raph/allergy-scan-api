<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScanLogResource;
use App\Models\ScanLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = $request->user()
            ->scanLogs()
            ->with(['product.allergens', 'profile'])
            ->latest('created_at')
            ->paginate(20);

        return success_response(ScanLogResource::collection($logs));
    }

    public function show(Request $request, ScanLog $scanLog): JsonResponse
    {
        abort_if($scanLog->user_id !== $request->user()->id, 403, 'Forbidden');

        $scanLog->load(['product.allergens', 'profile']);

        return success_response(new ScanLogResource($scanLog));
    }
}