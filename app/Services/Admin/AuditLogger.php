<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an administrative action.
     *
     * @param string $action
     * @param Model|string $auditable The model instance, or a string identifier if no model exists (e.g. settings key)
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param int|null $auditableId Explicit ID if auditable is a string
     */
    public static function log(string $action, $auditable, ?array $oldValues = null, ?array $newValues = null, ?int $auditableId = null): void
    {
        $type = $auditable instanceof Model ? get_class($auditable) : $auditable;
        $id = $auditable instanceof Model ? $auditable->getKey() : ($auditableId ?? 0);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $type,
            'auditable_id' => $id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
