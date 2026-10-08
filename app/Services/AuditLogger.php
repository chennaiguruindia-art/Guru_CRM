<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(string $action, string $module, ?int $recordId = null, ?array $oldValues = null, ?array $newValues = null): ?AuditLog
    {
        try {
            return AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Fail gracefully if audit logging encounters an issue
            report($e);
            return null;
        }
    }
}
