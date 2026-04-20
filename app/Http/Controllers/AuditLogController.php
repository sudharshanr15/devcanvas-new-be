<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use \App\Helpers\ApiResponse;

    public function index(Request $request)
    {
        $user = auth()->user();

        // param: resource_type
        $params = $request->validate([
            "resource_type" => ["nullable", "string"]
        ]);
        $resource_type = $params["resource_type"] ?? null;
        if ($resource_type) {
            $logs = AuditLog::where('user_id', $user->id)
                ->where('resource_type', $resource_type)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();
        } else {

            $logs = AuditLog::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();
        }

        return $this->successResponse($logs);
    }

    public function show(string $resource_id)
    {
        $user = auth()->user();
        $logs = AuditLog::where('user_id', $user->id)
            ->where('resource_id', $resource_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($logs);
    }
}
