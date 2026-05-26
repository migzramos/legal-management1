<?php
namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\LegalCase;
use App\Models\Revenue;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $lawyerId = auth()->id();
        $activeStatuses = ['intake', 'barangay_mediation', 'escalation_to_court', 'active_case'];
    
        $activeCases = LegalCase::where('lawyer_id', $lawyerId)
            ->whereIn('status', $activeStatuses)
            ->count();

        $revenueThisMonth = Revenue::where('lawyer_id', $lawyerId)
            ->whereYear('revenue_date', now()->year)
            ->whereMonth('revenue_date', now()->month)
            ->sum('amount');

        $billableHours = TimeEntry::where('lawyer_id', $lawyerId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('hours');

        $totalClients = LegalCase::where('lawyer_id', $lawyerId)
            ->distinct('client_id')
            ->count('client_id');

        $cases = LegalCase::with(['client:id,name', 'category:id,name'])
            ->where('lawyer_id', $lawyerId)
            ->whereIn('status', $activeStatuses)
            ->latest('updated_at')
            ->take(5)->get();

        $upcomingSchedules = Schedule::upcoming()
            ->whereHas('case', function ($q) use ($lawyerId) {
                $q->where('lawyer_id', $lawyerId);
            })
            ->with('case:id,title')
            ->orderBy('scheduled_at')
            ->take(5)->get();

        $todayTasks = Task::with('case:id,title')
            ->where('assigned_to', $lawyerId)
            ->whereDate('due_date', today())
            ->orderBy('due_date')
            ->take(5)->get();

        $upcomingAppointments = Appointment::with('client:id,name')
            ->where('lawyer_id', $lawyerId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_at', '>=', now())
            ->orderBy('appointment_at')
            ->take(5)
            ->get();

        $recentInvoices = Invoice::with('client:id,name')
            ->where('lawyer_id', $lawyerId)
            ->latest('created_at')
            ->take(4)->get();

        $totalRevenue = Revenue::where('lawyer_id', $lawyerId)->sum('amount');
        $pendingInvoices = Invoice::where('lawyer_id', $lawyerId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->count();

        // Today's priorities
        $hearingsToday = Schedule::whereHas('case', function ($q) use ($lawyerId) {
            $q->where('lawyer_id', $lawyerId);
        })
        ->whereDate('scheduled_at', today())
        ->where('status', 'upcoming')
        ->with('case:id,title')
        ->get();

        $deadlinesToday = Task::where('assigned_to', $lawyerId)
            ->whereDate('due_date', today())
            ->with('case:id,title')
            ->get();

        $unpaidClients = LegalCase::where('lawyer_id', $lawyerId)
            ->whereHas('invoices', function ($q) {
                $q->where('status', '!=', 'paid');
            })
            ->with('client:id,name')
            ->distinct('client_id')
            ->get();

        // Urgent items
        $overdueTasks = Task::where('assigned_to', $lawyerId)
            ->where('due_date', '<', today())
            ->where('status', '!=', 'completed')
            ->with('case:id,title')
            ->get();

        $unpaidInvoices = Invoice::where('lawyer_id', $lawyerId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->where('due_date', '<', today())
            ->with('client:id,name')
            ->get();

        return view('lawyer.dashboard', compact(
            'activeCases', 'revenueThisMonth', 'billableHours', 'totalClients',
            'cases', 'upcomingSchedules', 'todayTasks', 'recentInvoices', 'upcomingAppointments',
            'totalRevenue', 'pendingInvoices', 'hearingsToday', 'deadlinesToday', 'unpaidClients',
            'overdueTasks', 'unpaidInvoices'
        ));
    }
}