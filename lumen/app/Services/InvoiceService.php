<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;

class InvoiceService
{
    public static function handle_invoice($transaction, $account, $amount)
    {
        // TODO acho que fazer o mesmo for do payments resolve
        $due_date = (new Carbon($transaction->invoice_first_charge))->day($account->invoice_due_date);
        $due_date = date("Y/m/d", strtotime($due_date));

        $invoice = Invoice::where('account_id', $account->id)
            ->where('due_date', $due_date)->get()->first();

        if (!$invoice){
            return self::insert_new_invoice($account->id, $amount, $due_date);
        }

        $invoice->update([
            'amount' => $invoice->amount + $amount
        ]);
        return $invoice;
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
