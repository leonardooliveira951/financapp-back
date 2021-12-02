<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;

class InvoiceService
{
    public static function handle_invoice($transaction, $account, $payment)
    {
        $due_date = $transaction->invoice_first_charge . '/' . $account->invoice_due_date;
        $due_date = date("Y/m/d", strtotime($due_date));

        $invoice = Invoice::where('account_id', $account->id)
            ->where('due_date', $due_date);
        if (!$invoice){
            return self::insert_new_invoice($account->id, $payment->amount, $due_date);
        }

        $invoice->update([
            'amount' => $invoice->amount + $payment->amount
        ]);
    }

    private static function insert_new_invoice($account_id, $amount, $due_date)
    {
        $invoice = new Invoice();
        $invoice->account_id = $account_id;
        $invoice->amount = $amount;
        $invoice->due_date = $due_date;
        $invoice->save();

        return $invoice;
    }
}
