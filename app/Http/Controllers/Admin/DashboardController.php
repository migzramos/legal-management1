<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\LegalCase;
use App\Models\Task;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Revenue;
use App\Models\AdminMessage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue   = Revenue::sum('amount');
        $revenueThisMonth = Revenue::whereMonth('revenue_date', now()->month)->sum('amount');
        $activeCases    = LegalCase::whereIn('status', ['open', 'ongoing'])->count();
        $totalUsers     = User::count();
        $clientSatisfaction = 4.8;

        $wonCases    = LegalCase::where('status', 'won')->count();
        $closedCases = LegalCase::whereIn('status', ['closed', 'won', 'lost', 'dismissed'])->count();
        $successRate = $closedCases > 0 ? round(($wonCases / $closedCases) * 100) : 94;

        $lawyers = User::where('role', 'lawyer')
            ->withCount(['lawyerCases as active_cases_count' => function ($q) {
                $q->whereIn('status', ['open', 'ongoing']);
            }])
            ->get();

        $recentCases = LegalCase::with(['client:id,name', 'lawyer:id,name', 'category:id,name'])
            ->latest()->take(5)->get();

        $auditLogs = AuditLog::with('user:id,name,role')
            ->latest()->take(5)->get();

        $upcomingSchedules = Schedule::with(['case:id,title,case_number'])
            ->where('scheduled_at', '>=', now())
            ->where('status', 'upcoming')
            ->orderBy('scheduled_at')
            ->take(5)->get();

        $casesByType = LegalCase::with('category:id,name')
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->take(5)->get();

        $totalLawyers = User::where('role', 'lawyer')->count();
        $totalClients = User::where('role', 'client')->count();
        $pendingInvoices = Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum('balance');
        $courtHearingsThisWeek = Schedule::where('type', 'court_hearing')
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $deadlinesThisWeek = Schedule::where('type', 'deadline')
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $meetingsThisWeek = Schedule::where('type', 'meeting')
            ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $unreadAdminMessages = AdminMessage::unread()->count();
        $unresolvedMessages = AdminMessage::unresolved()->count();

        return view('admin.dashboard', compact(
            'totalRevenue', 'revenueThisMonth', 'activeCases', 'totalUsers', 'clientSatisfaction',
            'successRate', 'lawyers', 'recentCases', 'auditLogs',
            'upcomingSchedules', 'casesByType', 'totalLawyers', 'totalClients',
            'pendingInvoices', 'courtHearingsThisWeek', 'deadlinesThisWeek',
            'meetingsThisWeek', 'wonCases', 'closedCases',
            'unreadAdminMessages', 'unresolvedMessages'
        ));
    }
}