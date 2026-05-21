<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\LegalCase;
use App\Models\Task;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Return the reports overview as JSON (used by API/AJAX).
     */
    public function overview(): JsonResponse
    {
        $totalCases   = LegalCase::count();
        $openCases    = LegalCase::where('status', 'open')->count();
        $ongoingCases = LegalCase::where('status', 'ongoing')->count();
        $wonCases     = LegalCase::where('status', 'won')->count();
        $lostCases    = LegalCase::where('status', 'lost')->count();
        $closedCases  = LegalCase::whereIn('status', ['closed', 'won', 'lost', 'dismissed'])->count();

        $winRatio = $closedCases > 0
            ? round(($wonCases / $closedCases) * 100, 2)
            : 0;

        $pendingTasks = Task::where('status', 'pending')->count();
        $overdueTasks = Task::where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $totalRevenue  = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum('balance');

        $totalLawyers = User::where('role', 'lawyer')->count();
        $totalClients = User::where('role', 'client')->count();

        return response()->json([
            'cases' => [
                'total'   => $totalCases,
                'open'    => $openCases,
                'ongoing' => $ongoingCases,
                'won'     => $wonCases,
                'lost'    => $lostCases,
                'closed'  => $closedCases,
            ],
            'win_loss_ratio' => [
                'win_percentage'  => $winRatio,
                'loss_percentage' => $closedCases > 0 ? round(100 - $winRatio, 2) : 0,
            ],
            'tasks' => [
                'pending' => $pendingTasks,
                'overdue' => $overdueTasks,
            ],
            'billing' => [
                'total_revenue'  => $totalRevenue,
                'pending_amount' => $pendingAmount,
            ],
            'users' => [
                'lawyers' => $totalLawyers,
                'clients' => $totalClients,
            ],
        ]);
    }

    /**
     * Render the reports dashboard Blade view.
     */
    public function page()
    {
        $totalCases   = LegalCase::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');

        $wonCases       = LegalCase::where('status', 'won')->count();
        $lostCases      = LegalCase::where('status', 'lost')->count();
        $ongoingCases   = LegalCase::where('status', 'ongoing')->count();
        $dismissedCases = LegalCase::where('status', 'dismissed')->count();
        $closedCases    = LegalCase::whereIn('status', ['closed', 'won', 'lost', 'dismissed'])->count();

        $winRate      = $closedCases > 0 ? round(($wonCases / $closedCases) * 100) : 0;
        $avgCaseValue = $totalCases > 0 ? round($totalRevenue / $totalCases) : 0;

        // Pending & overdue tasks
        $pendingTasks = Task::where('status', 'pending')->count();
        $overdueTasks = Task::where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        // Pending billing
        $pendingAmount = Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum('balance');

        // Lawyer performance: real revenue from paid invoices, real win rate
        $lawyers = User::where('role', 'lawyer')
            ->withCount([
                'lawyerCases as active_cases' => fn ($q) => $q->whereIn('status', ['open', 'ongoing']),
                'lawyerCases as total_cases',
                'lawyerCases as won_cases'    => fn ($q) => $q->where('status', 'won'),
            ])
            ->with([
                // Sum paid invoices for cases this lawyer is assigned to
                'lawyerCases' => fn ($q) => $q->with(['invoices' => fn ($q) => $q->where('status', 'paid')]),
            ])
            ->get()
            ->map(function ($lawyer) {
                $revenue  = $lawyer->lawyerCases->flatMap->invoices->sum('total');
                $winRate  = $lawyer->total_cases > 0
                    ? round(($lawyer->won_cases / $lawyer->total_cases) * 100)
                    : 0;

                return [
                    'name'         => $lawyer->name,
                    'active_cases' => $lawyer->active_cases,
                    'total_cases'  => $lawyer->total_cases,
                    'won_cases'    => $lawyer->won_cases,
                    'revenue'      => $revenue,
                    'win_rate'     => $winRate,
                ];
            });

        // Last 6 calendar months for the chart
        // Uses updated_at as the date proxy for paid invoices (no paid_at column in schema)
        $chartData = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end   = $month->copy()->endOfMonth();

            return [
                'label'   => $month->format('M'),
                'cases'   => LegalCase::whereBetween('created_at', [$start, $end])->count(),
                'revenue' => Invoice::where('status', 'paid')
                    ->whereBetween('updated_at', [$start, $end])
                    ->sum('total'),
            ];
        });

        $months     = $chartData->pluck('label')->toArray();
        $caseCounts = $chartData->pluck('cases')->toArray();
        $revenues   = $chartData->pluck('revenue')->toArray();

        return view('admin.reports', compact(
            'totalCases', 'totalRevenue', 'winRate', 'avgCaseValue',
            'lawyers', 'months', 'caseCounts', 'revenues',
            'wonCases', 'ongoingCases', 'lostCases', 'dismissedCases', 'closedCases',
            'pendingTasks', 'overdueTasks', 'pendingAmount'
        ));
    }

    /**
     * Download a PDF export of the current reports.
     */
    public function export()
    {
        $totalCases   = LegalCase::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');

        $wonCases       = LegalCase::where('status', 'won')->count();
        $lostCases      = LegalCase::where('status', 'lost')->count();
        $ongoingCases   = LegalCase::where('status', 'ongoing')->count();
        $dismissedCases = LegalCase::where('status', 'dismissed')->count();
        $closedCases    = LegalCase::whereIn('status', ['closed', 'won', 'lost', 'dismissed'])->count();

        $winRate      = $closedCases > 0 ? round(($wonCases / $closedCases) * 100) : 0;
        $avgCaseValue = $totalCases > 0 ? round($totalRevenue / $totalCases) : 0;

        $pendingTasks  = Task::where('status', 'pending')->count();
        $overdueTasks  = Task::where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        $pendingAmount = Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum('balance');

        $lawyers = User::where('role', 'lawyer')
            ->withCount([
                'lawyerCases as active_cases' => fn ($q) => $q->whereIn('status', ['open', 'ongoing']),
                'lawyerCases as total_cases',
                'lawyerCases as won_cases'    => fn ($q) => $q->where('status', 'won'),
            ])
            ->with([
                'lawyerCases' => fn ($q) => $q->with(['invoices' => fn ($q) => $q->where('status', 'paid')]),
            ])
            ->get()
            ->map(function ($lawyer) {
                $revenue = $lawyer->lawyerCases->flatMap->invoices->sum('total');
                $winRate = $lawyer->total_cases > 0
                    ? round(($lawyer->won_cases / $lawyer->total_cases) * 100)
                    : 0;

                return [
                    'name'         => $lawyer->name,
                    'active_cases' => $lawyer->active_cases,
                    'total_cases'  => $lawyer->total_cases,
                    'won_cases'    => $lawyer->won_cases,
                    'revenue'      => $revenue,
                    'win_rate'     => $winRate,
                ];
            });

        $chartData = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end   = $month->copy()->endOfMonth();

            return [
                'label'   => $month->format('M'),
                'cases'   => LegalCase::whereBetween('created_at', [$start, $end])->count(),
                'revenue' => Invoice::where('status', 'paid')
                    ->whereBetween('updated_at', [$start, $end])
                    ->sum('total'),
            ];
        });

        $months     = $chartData->pluck('label')->toArray();
        $caseCounts = $chartData->pluck('cases')->toArray();
        $revenues   = $chartData->pluck('revenue')->toArray();

        $pdf = Pdf::loadView('admin.reports-pdf', compact(
            'totalCases', 'totalRevenue', 'winRate', 'avgCaseValue',
            'lawyers', 'months', 'caseCounts', 'revenues',
            'wonCases', 'ongoingCases', 'lostCases', 'dismissedCases', 'closedCases',
            'pendingTasks', 'overdueTasks', 'pendingAmount'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('firm-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Paginated audit log JSON (for AJAX table).
     */
    public function auditLogs(): JsonResponse
    {
        $logs = AuditLog::with('user:id,name,email,role')
            ->latest()
            ->paginate(30);

        return response()->json($logs);
    }
}