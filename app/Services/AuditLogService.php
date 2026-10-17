<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    /**
     * Log a critical admin or security event.
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $metadata = null,
        ?int $schoolId = null
    ): AuditLog {
        return AuditLog::create([
            'actor_user_id' => auth()->id(),
            'school_id' => $schoolId ?? session('current_school_id'),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
