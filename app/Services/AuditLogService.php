<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * Log aktivitas ke audit log
     */
    public function log(
        string $action,
        string $entity,
        ?int $entityId = null,
        ?array $beforeData = null,
        ?array $afterData = null
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => Auth::id(),
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log create action
     */
    public function logCreate(string $entity, int $entityId, array $data): AuditLog
    {
        return $this->log('create', $entity, $entityId, null, $data);
    }

    /**
     * Log update action
     */
    public function logUpdate(string $entity, int $entityId, array $before, array $after): AuditLog
    {
        return $this->log('update', $entity, $entityId, $before, $after);
    }

    /**
     * Log delete action
     */
    public function logDelete(string $entity, int $entityId, array $data): AuditLog
    {
        return $this->log('delete', $entity, $entityId, $data, null);
    }

    /**
     * Log approval action
     */
    public function logApprove(string $entity, int $entityId, ?string $note = null): AuditLog
    {
        return $this->log('approve', $entity, $entityId, null, ['note' => $note]);
    }

    /**
     * Log rejection action
     */
    public function logReject(string $entity, int $entityId, ?string $note = null): AuditLog
    {
        return $this->log('reject', $entity, $entityId, null, ['note' => $note]);
    }
}
