<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;

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

}
