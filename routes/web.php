<?php
 
use Illuminate\Support\Facades\Route;
 
Route::get('/', function () {
    return view('auth.landingpage');
})->name('home');
 
Route::middleware(['auth', 'active.user'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
 
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
 
        if ($user->isLawyer()) {
            return redirect()->route('lawyer.dashboard');
        }
 
        return redirect()->route('client.dashboard');
    })->name('dashboard');
});
 
Route::middleware(['auth', 'active.user', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/dashboard');
 
        Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');
 
        Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])
            ->name('users.index');
        Route::get('users/export', [\App\Http\Controllers\Admin\UserController::class, 'export'])
            ->name('users.export');
        Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])
            ->name('users.store');
        Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])
            ->name('users.show');
        Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])
            ->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])
            ->name('users.destroy');
        Route::patch('users/{user}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])
            ->name('users.toggle-active');
        Route::post('users/{user}/assign-lawyer', [\App\Http\Controllers\Admin\UserController::class, 'assignLawyer'])
            ->name('users.assign-lawyer');
 
        Route::get('calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])
            ->name('calendar');
 
        Route::post('calendar/schedules', [\App\Http\Controllers\Admin\CalendarController::class, 'store'])
            ->name('calendar.store');
 
        // ─── Reports ──────────────────────────────────────────────────────────
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'page'])
            ->name('reports.page');
        Route::get('reports/overview', [\App\Http\Controllers\Admin\ReportController::class, 'overview'])
            ->name('reports.overview');
        Route::get('reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])
            ->name('reports.export');
        Route::get('reports/audit-logs', [\App\Http\Controllers\Admin\ReportController::class, 'auditLogs'])
            ->name('reports.audit-logs');
        // ──────────────────────────────────────────────────────────────────────
 
        Route::get('messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])
            ->name('messages');
        Route::post('messages', [\App\Http\Controllers\Admin\MessageController::class, 'store'])
            ->name('messages.store');
 
        Route::get('lawyer-messages', [\App\Http\Controllers\Admin\AdminMessageController::class, 'index'])
            ->name('lawyer-messages.index');
        Route::get('lawyer-messages/{user}', [\App\Http\Controllers\Admin\AdminMessageController::class, 'getConversation'])
            ->name('lawyer-messages.conversation');
        Route::post('lawyer-messages/{user}', [\App\Http\Controllers\Admin\AdminMessageController::class, 'send'])
            ->name('lawyer-messages.send');
        Route::post('lawyer-messages/{user}/mark-read', [\App\Http\Controllers\Admin\AdminMessageController::class, 'markAsRead'])
            ->name('lawyer-messages.mark-read');
        Route::delete('lawyer-messages/{adminMessage}', [\App\Http\Controllers\Admin\AdminMessageController::class, 'delete'])
            ->name('lawyer-messages.delete');
 
        Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])
            ->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])
            ->name('audit-logs.show');
 
        Route::get('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])
            ->name('notifications');
        Route::post('notifications/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])
            ->name('notifications.read');
 
        Route::get('categories', [\App\Http\Controllers\Admin\CaseCategoryController::class, 'index'])
            ->name('categories.index');
        Route::post('categories', [\App\Http\Controllers\Admin\CaseCategoryController::class, 'store'])
            ->name('categories.store');
        Route::put('categories/{caseCategory}', [\App\Http\Controllers\Admin\CaseCategoryController::class, 'update'])
            ->name('categories.update');
        Route::delete('categories/{caseCategory}', [\App\Http\Controllers\Admin\CaseCategoryController::class, 'destroy'])
            ->name('categories.destroy');
 
        Route::get('court-types', [\App\Http\Controllers\Admin\CourtTypeController::class, 'index'])
            ->name('court-types.index');
        Route::post('court-types', [\App\Http\Controllers\Admin\CourtTypeController::class, 'store'])
            ->name('court-types.store');
        Route::put('court-types/{courtType}', [\App\Http\Controllers\Admin\CourtTypeController::class, 'update'])
            ->name('court-types.update');
        Route::delete('court-types/{courtType}', [\App\Http\Controllers\Admin\CourtTypeController::class, 'destroy'])
            ->name('court-types.destroy');
 
        Route::get('billing-rates', [\App\Http\Controllers\Admin\BillingRateController::class, 'index'])
            ->name('billing-rates.index');
        Route::post('billing-rates', [\App\Http\Controllers\Admin\BillingRateController::class, 'store'])
            ->name('billing-rates.store');
        Route::delete('billing-rates/{billingRate}', [\App\Http\Controllers\Admin\BillingRateController::class, 'destroy'])
            ->name('billing-rates.destroy');
    });
 
Route::middleware(['auth', 'active.user', 'lawyer'])
    ->prefix('lawyer')
    ->name('lawyer.')
    ->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Lawyer\DashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('profile', [\App\Http\Controllers\Lawyer\LawyerProfileController::class, 'show'])
            ->name('profile');
        Route::put('profile', [\App\Http\Controllers\Lawyer\LawyerProfileController::class, 'update'])
            ->name('profile.update');
        Route::get('cases', [\App\Http\Controllers\Lawyer\CaseController::class, 'index'])
            ->name('cases.index');
        Route::get('cases/create', [\App\Http\Controllers\Lawyer\CaseController::class, 'create'])
            ->name('cases.create');
        Route::post('cases', [\App\Http\Controllers\Lawyer\CaseController::class, 'store'])
            ->name('cases.store');
        Route::get('cases/{case}', [\App\Http\Controllers\Lawyer\CaseController::class, 'show'])
            ->name('cases.show');
        Route::get('cases/{case}/edit', [\App\Http\Controllers\Lawyer\CaseController::class, 'edit'])
            ->name('cases.edit');
        Route::put('cases/{case}', [\App\Http\Controllers\Lawyer\CaseController::class, 'update'])
            ->name('cases.update');
        Route::delete('cases/{case}', [\App\Http\Controllers\Lawyer\CaseController::class, 'destroy'])
            ->name('cases.destroy');
        Route::patch('cases/{case}/status/{status}', [\App\Http\Controllers\Lawyer\CaseController::class, 'updateStatus'])
            ->name('cases.status');
        Route::get('cases/{case}/documents', [\App\Http\Controllers\Lawyer\DocumentController::class, 'index'])
            ->name('documents.index');
        Route::post('documents', [\App\Http\Controllers\Lawyer\DocumentController::class, 'store'])
            ->name('documents.store');
        Route::get('documents/{document}', [\App\Http\Controllers\Lawyer\DocumentController::class, 'show'])
            ->name('documents.show');
        Route::get('documents/{document}/download', [\App\Http\Controllers\Lawyer\DocumentController::class, 'download'])
            ->name('documents.download');
        Route::delete('documents/{document}', [\App\Http\Controllers\Lawyer\DocumentController::class, 'destroy'])
            ->name('documents.destroy');
        Route::patch('documents/{document}/toggle-visibility', [\App\Http\Controllers\Lawyer\DocumentController::class, 'toggleVisibility'])
            ->name('documents.toggle-visibility');
        Route::get('cases/{case}/tasks', [\App\Http\Controllers\Lawyer\TaskController::class, 'index'])
            ->name('tasks.index');
        Route::post('tasks', [\App\Http\Controllers\Lawyer\TaskController::class, 'store'])
            ->name('tasks.store');
        Route::put('tasks/{task}', [\App\Http\Controllers\Lawyer\TaskController::class, 'update'])
            ->name('tasks.update');
        Route::delete('tasks/{task}', [\App\Http\Controllers\Lawyer\TaskController::class, 'destroy'])
            ->name('tasks.destroy');
        Route::get('cases/{case}/schedules', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'index'])
            ->name('schedules.index');
        Route::post('schedules', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'store'])
            ->name('schedules.store');
        Route::put('schedules/{schedule}', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'update'])
            ->name('schedules.update');
        Route::delete('schedules/{schedule}', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'destroy'])
            ->name('schedules.destroy');
        Route::get('cases/{case}/time-entries', [\App\Http\Controllers\Lawyer\TimeEntryController::class, 'index'])
            ->name('time-entries.index');
        Route::post('time-entries', [\App\Http\Controllers\Lawyer\TimeEntryController::class, 'store'])
            ->name('time-entries.store');
        Route::delete('time-entries/{timeEntry}', [\App\Http\Controllers\Lawyer\TimeEntryController::class, 'destroy'])
            ->name('time-entries.destroy');
        Route::get('billing', [\App\Http\Controllers\Lawyer\BillingController::class, 'index'])
            ->name('billing.index');
        Route::get('billing/invoices/create', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'create'])
            ->name('billing.invoices.create');
        Route::post('billing/invoices', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'store'])
            ->name('billing.invoices.store');
        Route::get('billing/invoices/{invoice}', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'show'])
            ->name('billing.invoices.show');
        Route::get('billing/invoices/{invoice}/pdf', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'downloadPdf'])
            ->name('billing.invoices.pdf');
        Route::get('billing/invoices/{invoice}/edit', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'edit'])
            ->name('billing.invoices.edit');
        Route::put('billing/invoices/{invoice}', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'update'])
            ->name('billing.invoices.update');
        Route::post('billing/invoices/{invoice}/payment', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'recordPayment'])
            ->name('billing.invoices.payment');
        Route::patch('billing/invoices/{invoice}/validate', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'validateInvoice'])
            ->name('billing.invoices.validate');
        Route::delete('billing/invoices/{invoice}', [\App\Http\Controllers\Lawyer\InvoiceController::class, 'destroy'])
            ->name('billing.invoices.destroy');
        Route::get('billing/payment-methods', [\App\Http\Controllers\Lawyer\BillingController::class, 'paymentMethods'])
            ->name('billing.payment-methods');
 
        // ─── Payment Transaction Confirm / Reject ─────────────────────────────
        Route::patch('billing/transactions/{transaction}/confirm', [\App\Http\Controllers\Lawyer\PaymentTransactionController::class, 'confirm'])
            ->name('billing.transactions.confirm');
        Route::patch('billing/transactions/{transaction}/reject', [\App\Http\Controllers\Lawyer\PaymentTransactionController::class, 'reject'])
            ->name('billing.transactions.reject');
        // ──────────────────────────────────────────────────────────────────────
 
        Route::get('appointments', [\App\Http\Controllers\Lawyer\AppointmentController::class, 'index'])
            ->name('appointments.index');
        Route::get('appointments/{appointment}', [\App\Http\Controllers\Lawyer\AppointmentController::class, 'show'])
            ->name('appointments.show');
        Route::patch('appointments/{appointment}/confirm', [\App\Http\Controllers\Lawyer\AppointmentController::class, 'confirm'])
            ->name('appointments.confirm');
        Route::patch('appointments/{appointment}/cancel', [\App\Http\Controllers\Lawyer\AppointmentController::class, 'cancel'])
            ->name('appointments.cancel');
        Route::get('calendar', [\App\Http\Controllers\Lawyer\CalendarController::class, 'index'])
            ->name('calendar.index');
        Route::get('schedules/create', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'create'])
            ->name('schedules.create');
        Route::get('availability', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'availabilityIndex'])
            ->name('availability.index');
        Route::post('availability', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'storeAvailability'])
            ->name('availability.store');
        Route::delete('availability/{availability}', [\App\Http\Controllers\Lawyer\ScheduleController::class, 'destroyAvailability'])
            ->name('availability.destroy');
        Route::get('messages', [\App\Http\Controllers\Lawyer\MessageController::class, 'list'])
            ->name('messages.list');
        Route::get('cases/{case}/messages', [\App\Http\Controllers\Lawyer\MessageController::class, 'index'])
            ->name('messages.index');
        Route::post('messages', [\App\Http\Controllers\Lawyer\MessageController::class, 'store'])
            ->name('messages.store');
        Route::get('notifications', [\App\Http\Controllers\Lawyer\NotificationController::class, 'index'])
            ->name('notifications');
        Route::post('notifications/read', [\App\Http\Controllers\Lawyer\NotificationController::class, 'markRead'])
            ->name('notifications.read');
        Route::get('admin-messages', [\App\Http\Controllers\Lawyer\LawyerMessagingController::class, 'myMessages'])
            ->name('admin-messages.index');
        Route::get('admin-messages/{adminMessage}', [\App\Http\Controllers\Lawyer\LawyerMessagingController::class, 'show'])
            ->name('admin-messages.show');
        Route::post('admin-messages/send', [\App\Http\Controllers\Lawyer\LawyerMessagingController::class, 'sendToAdmin'])
            ->name('admin-messages.send');
        Route::get('payments/pending', [\App\Http\Controllers\Lawyer\PaymentTransactionController::class, 'getPendingPayments'])
            ->name('payments.pending');
        Route::patch('payments/{paymentTransaction}/confirm', [\App\Http\Controllers\Lawyer\PaymentTransactionController::class, 'confirmPayment'])
            ->name('payments.confirm');
        Route::get('appointments/{appointment}/messages', [\App\Http\Controllers\Lawyer\MessageController::class, 'appointmentThread'])
            ->name('appointments.messages');
    });
 
Route::middleware(['auth', 'active.user', 'client'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Client\DashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('profile', [\App\Http\Controllers\Client\ProfileController::class, 'show'])
            ->name('profile');
        Route::put('profile', [\App\Http\Controllers\Client\ProfileController::class, 'update'])
            ->name('profile.update');
        Route::get('cases', [\App\Http\Controllers\Client\CaseController::class, 'index'])
            ->name('cases.index');
        Route::get('cases/{case}', [\App\Http\Controllers\Client\CaseController::class, 'show'])
            ->name('cases.show');
        Route::get('cases/{case}/progress', [\App\Http\Controllers\Client\CaseController::class, 'progress'])
            ->name('cases.progress');
        Route::get('cases/{case}/documents', [\App\Http\Controllers\Client\DocumentController::class, 'index'])
            ->name('documents.index');
        Route::get('documents/{document}', [\App\Http\Controllers\Client\DocumentController::class, 'show'])
            ->name('documents.show');
        Route::get('documents/{document}/download', [\App\Http\Controllers\Client\DocumentController::class, 'download'])
            ->name('documents.download');
        Route::post('cases/{case}/documents/upload', [\App\Http\Controllers\Client\DocumentController::class, 'upload'])
            ->name('documents.upload');
        Route::get('invoices', [\App\Http\Controllers\Client\InvoiceController::class, 'index'])
            ->name('invoices.index');
        Route::get('invoices/{invoice}', [\App\Http\Controllers\Client\InvoiceController::class, 'show'])
            ->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Client\InvoiceController::class, 'downloadPdf'])
            ->name('invoices.pdf');
        Route::get('messages', [\App\Http\Controllers\Client\MessageController::class, 'index'])
            ->name('messages.list');
        Route::get('messages/{case}', [\App\Http\Controllers\Client\MessageController::class, 'index'])
            ->name('messages.index');
        Route::post('messages', [\App\Http\Controllers\Client\MessageController::class, 'store'])
            ->name('messages.store');
        Route::get('appointments', [\App\Http\Controllers\Client\AppointmentController::class, 'index'])
            ->name('appointments.index');
        Route::post('appointments', [\App\Http\Controllers\Client\AppointmentController::class, 'store'])
            ->name('appointments.store');
        Route::get('appointments/{appointment}', [\App\Http\Controllers\Client\AppointmentController::class, 'show'])
            ->name('appointments.show');
        Route::patch('appointments/{appointment}/cancel', [\App\Http\Controllers\Client\AppointmentController::class, 'cancel'])
            ->name('appointments.cancel');
        Route::get('notifications', [\App\Http\Controllers\Client\NotificationController::class, 'index'])
            ->name('notifications');
        Route::post('notifications/read', [\App\Http\Controllers\Client\NotificationController::class, 'markRead'])
            ->name('notifications.read');
        Route::get('appointments/{appointment}/messages', [\App\Http\Controllers\Client\MessageController::class, 'appointmentThread'])
            ->name('appointments.messages');
        Route::post('invoices/{invoice}/pay', [\App\Http\Controllers\Client\PaymentController::class, 'initiate'])
            ->name('invoices.pay');
        Route::get('payments/success', [\App\Http\Controllers\Client\PaymentController::class, 'success'])
            ->name('payments.success');
        Route::get('payments/failed', [\App\Http\Controllers\Client\PaymentController::class, 'failed'])
            ->name('payments.failed');
    });
 
require __DIR__.'/auth.php';
 