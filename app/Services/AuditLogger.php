<?php
namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\LegalCase;
use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\Document;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function log(
        string $action,
        Model $model,
        array $oldValues = [],
        array $newValues = []
    ): void {
        $description = self::generateDescription($action, $model, $oldValues, $newValues);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private static function generateDescription(string $action, Model $model, array $oldValues, array $newValues): string
    {
        if ($model instanceof User) {
            if ($action === 'created') {
                return "New user {$model->name} registered as " . ucfirst($model->role);
            } elseif ($action === 'updated') {
                return "User {$model->name}'s profile was updated";            } elseif ($action === 'lawyer_assigned') {
                $lawyer = User::find($newValues['lawyer_id']);
                return "Lawyer {$lawyer->name} assigned to client {$model->name}";            }
        } elseif ($model instanceof LegalCase) {
            if ($action === 'created') {
                return "New case {$model->case_number} created for {$model->client->name}";
            } elseif ($action === 'updated') {
                if (isset($newValues['status'])) {
                    return "Case {$model->case_number} status changed to " . ucfirst($newValues['status']);
                }
                return "Case {$model->case_number} was updated";
            }
        } elseif ($model instanceof Invoice) {
            if ($action === 'created') {
                return "Invoice #{$model->id} created for {$model->client->name}";
            } elseif ($action === 'updated') {
                if (isset($newValues['status']) && $newValues['status'] === 'sent') {
                    return "Invoice #{$model->id} sent to {$model->client->name}";
                } elseif (isset($newValues['status']) && $newValues['status'] === 'overdue') {
                    return "Invoice #{$model->id} for {$model->client->name} is overdue";
                }
                return "Invoice #{$model->id} was updated";
            }
        } elseif ($model instanceof Appointment) {
            if ($action === 'created') {
                return "New appointment booked by {$model->client->name}";
            }
        } elseif ($model instanceof Document) {
            if ($action === 'created') {
                return "Document uploaded for {$model->legalCase->case_number}";
            }
        } elseif ($model instanceof PaymentTransaction) {
            if ($action === 'created') {
                return "Payment received for Invoice #{$model->invoice->id}";
            }
        }

        // Fallback
        return ucfirst($action) . ' ' . class_basename($model) . ' by System';
    }
}