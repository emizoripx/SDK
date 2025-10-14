<?php

namespace Emizor\SDK\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Jobs\FetchInvoicePdf;
use Emizor\SDK\Jobs\Revocate;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = BeiInvoice::with('bei_account');

        // Filtros opcionales
        if ($request->has('status') && $request->status !== '') {
            $query->where('bei_step_emission', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('bei_emission_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('bei_emission_date', '<=', $request->date_to);
        }

        if ($request->has('account_id')) {
            $query->where('bei_account_id', $request->account_id);
        }

        $invoices = $query->paginate(20)->appends($request->query());

        $account = null;
        if ($request->has('account_id')) {
            $account = BeiAccount::find($request->account_id);
        }

        return view('emizor::admin.invoices.index', compact('invoices', 'account'));
    }

    public function show($id)
    {
        $invoice = BeiInvoice::with('bei_account')->findOrFail($id);

        return view('emizor::admin.invoices.show', compact('invoice'));
    }

    public function fetchPdf($id)
    {
        $invoice = BeiInvoice::findOrFail($id);

        if (!$invoice->hasPdf()) {
            FetchInvoicePdf::dispatch($invoice);
            return redirect()->back()->with('success', 'PDF fetch has been queued.');
        }

        return redirect()->back()->with('info', 'PDF is already available.');
    }

    public function revocate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|in:1,3',
        ]);

        $invoice = BeiInvoice::findOrFail($id);

        if ($invoice->isInProgress() || $invoice->isCompleteRevocation() || $invoice->isInProgressRevocation()) {
            return redirect()->back()->with('error', 'Invoice cannot be revoked at this time.');
        }

        try {
            Revocate::dispatch($invoice->getTicket(), (int) $request->reason);
            return redirect()->back()->with('success', 'Revocation request has been queued.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to queue revocation: ' . $e->getMessage());
        }
    }
}