<?php

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;

class BillingController extends Controller
{
    public function index()
    {
        $invoices = Invoice::where('lawyer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalOutstanding = Invoice::where('lawyer_id', auth()->id())
            ->where('status', 'unpaid')
            ->sum('total');

        $paidThisMonth = Invoice::where('lawyer_id', auth()->id())
            ->where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->sum('total');

        $pendingInvoices = Invoice::where('lawyer_id', auth()->id())
            ->where('status', 'unpaid')
            ->count();

        $overdueBalances = Invoice::where('lawyer_id', auth()->id())
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->sum('total');

        return view('lawyer.billing', compact(
            'invoices',
            'totalOutstanding',
            'paidThisMonth',
            'pendingInvoices',
            'overdueBalances'
        ));
    }

    public function paymentMethods()
    {
        return view('lawyer.payment-methods');
    }
}
