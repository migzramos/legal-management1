<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\LegalCase;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $clientId = auth()->id();

        // KPI: Active Cases
        $activeCases = LegalCase::where('client_id', $clientId)
            ->where('status', '!=', 'resolution')
            ->count();

        // KPI: Upcoming Appointments
        $upcomingAppointments = Appointment::where('client_id', $clientId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('appointment_at', '>=', now()->toDateString())
            ->count();

        // KPI: Unpaid Invoices
        $unpaidInvoices = Invoice::where('client_id', $clientId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->count();

        // KPI: Documents Count
        $documentsCount = Document::whereHas('case', function($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })->where('is_visible_to_client', true)->count();

        // Recent cases (latest 4)
        $recentCases = LegalCase::where('client_id', $clientId)
            ->with('lawyer')
            ->orderBy('updated_at', 'desc')
            ->limit(4)
            ->get();

        // Upcoming appointments (next 3)
        $appointments = Appointment::where('client_id', $clientId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('appointment_at', '>=', now()->toDateString())
            ->with('lawyer')
            ->orderBy('appointment_at', 'asc')
            ->limit(3)
            ->get();

        // Recent invoices (latest 4)
        $recentInvoices = Invoice::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return view('client.dashboard', compact(
            'activeCases',
            'upcomingAppointments',
            'unpaidInvoices',
            'documentsCount',
            'recentCases',
            'appointments',
            'recentInvoices'
        ));
    }
}