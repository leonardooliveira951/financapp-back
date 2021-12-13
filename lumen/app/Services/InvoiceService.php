<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public static function handleInvoice($due_date, $account_id, $amount)
    {
        $invoice = Invoice::where('account_id', $account_id)
            ->where('due_date', $due_date)->get()->first();

        if (!$invoice){
            return self::insertNewInvoice($account_id, $amount, $due_date);
        }

        $invoice->update([
            'amount' => $invoice->amount + $amount
        ]);
        return $invoice;
    }

    private static function insertNewInvoice($account_id, $amount, $due_date)
    {
        $invoice = new Invoice();
        $invoice->account_id = $account_id;
        $invoice->amount = $amount;
        $invoice->due_date = $due_date;
        $invoice->save();

        return $invoice;
    }

    public static function getInvoice($request)
    {
        $account = Account::where('id', $request->account_id)->get()->first();

        $due_date = strtotime($request->period . "-" . $account->invoice_due_date);
        $due_date = date("Y/m/d", $due_date);

        return Invoice::where('account_id', $account->id)
            ->where('due_date', $due_date)->get()->first();
    }

    public static function makeInvoicePayment($request)
    {
        $invoice = Invoice::where('id', $request->invoice_id)->get()->first();
        $paying_account = Account::where('id', $request->paying_account)->get()->first();

        if (!$invoice || !$paying_account){
            return 'Fatura ou conta não encontrados';

        }
        if ($invoice->status != 'closed'){
            return 'Fatura não está fechada';
        }

        $payment_difference = $invoice->amount - $request->amount;

        if ($payment_difference != 0){
            $due_date = strtotime("+1 month", strtotime($invoice->due_date));
            $due_date = date("Y/m/d", $due_date);

            self::handleInvoice($due_date, $invoice->account_id, $payment_difference);
        }

        TransactionService::insertInvoiceTransaction($invoice, $paying_account, $request->amount, $request->payment_date);

        $invoice_payments = Payment::where('invoice_id', $invoice->id)->get();

        foreach ($invoice_payments as $payment){
            $payment->update([
                'status' => 'done'
            ]);
        }
        $invoice->update([
            'status' => 'paid'
        ]);

        return 'Fatura paga com sucesso';
    }

    public static function changeInvoiceStatusToClosed()
    {
        $current_date = Carbon::now();

        $invoices = Invoice::where('due_date', '<=', $current_date)
            ->where('status', 'open')
            ->get();

        foreach ($invoices as $invoice)
        {
            $invoice->update([
                'status' => 'closed'
            ]);
        }
    }

}
