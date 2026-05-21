<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Base service for handling CRUD operations with strict error handling,
 * transactional support, and comprehensive audit logging
 */
abstract class BaseCrudService
{
    protected $modelClass;

    /**
     * Create a new resource with transaction support
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Validate input
            $data = $this->validateInput($data);

            // Create model
            $model = new $this->modelClass($data);

            // Before save hook
            $this->beforeCreate($model, $data);

            // Save model
            $model->save();

            // After save hook
            $this->afterCreate($model, $data);

            // Log action
            $this->logAction('create', $model, [], $data);

            return $model;
        });
    }

    /**
     * Update a resource with transaction support
     */
    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            // Validate input
            $data = $this->validateInput($data);

            // Store old values for audit log
            $oldValues = $model->toArray();

            // Before update hook
            $this->beforeUpdate($model, $data);

            // Update model
            $model->update($data);
            $model->refresh();

            // After update hook
            $this->afterUpdate($model, $data);

            // Log action
            $this->logAction('update', $model, $oldValues, $model->toArray());

            return $model;
        });
    }

    /**
     * Delete a resource with transaction support
     */
    public function delete(Model $model): bool
    {
        return DB::transaction(function () use ($model) {
            // Before delete hook
            $this->beforeDelete($model);

            // Store data before deletion for audit
            $deletedData = $model->toArray();

            // Delete model (soft or hard delete)
            $result = $model->delete();

            // After delete hook
            $this->afterDelete($model);

            // Log action
            if ($result) {
                $this->logAction('delete', $model, $deletedData, []);
            }

            return $result;
        });
    }

    /**
     * Restore a soft-deleted resource
     */
    public function restore(Model $model): bool
    {
        if (!method_exists($model, 'restore')) {
            return false;
        }

        return DB::transaction(function () use ($model) {
            $result = $model->restore();

            if ($result) {
                $this->logAction('restore', $model, [], $model->toArray());
            }

            return $result;
        });
    }

    /**
     * Permanently delete a resource
     */
    public function forceDelete(Model $model): bool
    {
        $deletedData = $model->toArray();

        $result = DB::transaction(function () use ($model) {
            return $model->forceDelete();
        });

        if ($result) {
            $this->logAction('force_delete', $model, $deletedData, []);
        }

        return $result;
    }

    /**
     * Validate input data (override in subclass)
     */
    protected function validateInput(array $data): array
    {
        return $data;
    }

    /**
     * Before create hook (override in subclass)
     */
    protected function beforeCreate(Model $model, array $data): void
    {
        // Override in subclass
    }

    /**
     * After create hook (override in subclass)
     */
    protected function afterCreate(Model $model, array $data): void
    {
        // Override in subclass
    }

    /**
     * Before update hook (override in subclass)
     */
    protected function beforeUpdate(Model $model, array $data): void
    {
        // Override in subclass
    }

    /**
     * After update hook (override in subclass)
     */
    protected function afterUpdate(Model $model, array $data): void
    {
        // Override in subclass
    }

    /**
     * Before delete hook (override in subclass)
     */
    protected function beforeDelete(Model $model): void
    {
        // Override in subclass
    }

    /**
     * After delete hook (override in subclass)
     */
    protected function afterDelete(Model $model): void
    {
        // Override in subclass
    }

    /**
     * Log action for audit trail
     */
    protected function logAction(string $action, Model $model, array $oldValues, array $newValues): void
    {
        try {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'entity_type' => class_basename($model),
                'entity_id' => $model->id,
                'action' => $action,
                'description' => "{$action} " . class_basename($model) . " {$model->id}",
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to log action: {$e->getMessage()}", [
                'action' => $action,
                'entity' => class_basename($model),
            ]);
        }
    }

    /**
     * Get paginated list with eager loading and filtering
     */
    public function paginate(array $filters = [], int $perPage = 15)
    {
        $query = $this->modelClass::query();

        // Apply filters (override in subclass)
        $query = $this->applyFilters($query, $filters);

        // Apply eager loading (override in subclass)
        $query = $this->eagerLoadRelations($query);

        // Apply ordering
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Apply query filters (override in subclass)
     */
    protected function applyFilters($query, array $filters)
    {
        return $query;
    }

    /**
     * Eager load relations (override in subclass)
     */
    protected function eagerLoadRelations($query)
    {
        return $query;
    }
}
