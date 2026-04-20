<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

trait AuditLogHelper
{
    protected function logAudit(
        string $action,
        string $resourceType,
        ?string $resourceId = null,
        ?string $resourceName = null,
        ?array $changes = null,
        ?Request $request = null
    ): AuditLog {
        $user = auth()->user();
        
        if (!$user) {
            return new AuditLog();
        }

        $ipAddress = $request?->ip() ?? request()?->ip();

        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'resource_name' => $resourceName,
            'changes' => $changes,
            'ip_address' => $ipAddress,
        ]);
    }
}
