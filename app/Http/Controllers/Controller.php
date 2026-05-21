<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Write an audit log entry.
     * Shared by all child controllers so they don't each need a private copy.
     */
    protected function auditLog(
        string $action,
        Model  $model,
        array  $old = [],
        array  $new = []
    ): void {
        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'model_type'  => get_class($model),
            'model_id'    => $model->getKey(),
            'description' => sprintf(
                '%s - %s #%s',
                ucfirst(str_replace('_', ' ', $action)),
                class_basename($model),
                $model->getKey()
            ),
            'old_values'  => $old ?: null,
            'new_values'  => $new ?: null,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}