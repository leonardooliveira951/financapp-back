<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;

class PaymentService
{
    public static function insertPayments($transaction)
    {
        $origin_account = Account::where('id', $transaction->origin_account_id)->get()->first();
        $destiny_account = Account::where('id', $transaction->destiny_account_id)->get()->first();

        if (!$destiny_account)
        {
            $payment = self::paymentProcess($transaction, $origin_account);
            return $payment;
        }

        $payment = self::transferProcess($transaction, $origin_account, $destiny_account);

        return $payment;
    }

    public static function paymentProcess($transaction, $account)
    {
        for ($x = 0; $x < $transaction->installments; $x++) {
            $transaction_date_timestamp = strtotime("+{$x} month", strtotime($transaction->date));
            $payment_date = date("Y/m/d", $transaction_date_timestamp);
            $amount = $transaction->amount / $transaction->installments;
            $transaction_id = $transaction->id;

            if ($account->type == 'credit_card'){
                $due_date = (new Carbon($transaction->invoice_first_charge))->day($account->invoice_due_date);
                $due_date = strtotime("+{$x} month", strtotime($due_date));
                $due_date = date("Y/m/d", $due_date);
                $invoice = InvoiceService::handleInvoice($due_date, $account->id, $amount);
            }

            $installment = $x + 1;
            $payment = self::schedulePayment($payment_date, $amount, $transaction_id, $installment, $invoice->id);

            $current_date = Carbon::now()->timestamp;
            if ((strtotime($payment_date) <= $current_date) && ($account->type != 'credit_card'))
            {
                self::makePayment($amount, $account, $transaction->type);
                $payment->update([
                    'status' => 'done'
                ]);
            }
        }
        return true;
    }

    public static function transferProcess($transaction, $origin_account, $destiny_account)
    {
        $payment = self::schedulePayment($transaction->date, $transaction->amount, $transaction->id, $transaction->installments);

        $current_date = Carbon::now()->timestamp;
        if (strtotime($transaction->date) <= $current_date)
        {
            self::makeTransfer($origin_account, $destiny_account, $payment->amount);
            $payment->update([
                'status' => 'done'
            ]);
        }
        return true;
    }

    public static function schedulePayment($payment_date, $amount, $transaction_id, $installment, $invoice_id = null)
    {
        $payment = new Payment();
        $payment->amount = $amount;
        $payment->date = $payment_date;
        $payment->transaction_id = $transaction_id;
        $payment->installment = $installment;
        $payment->invoice_id = $invoice_id;
        $payment->save();
        return $payment;
    }

    public static function makeScheduledPayment()
    {
        $current_date = Carbon::now();
        $payments = Payment::where('date', '<=', $current_date)
            ->where('status', 'scheduled')->get();

        foreach ($payments as $payment)
        {
            // TODO aqui é interessante fazer um JOIN entre as tabelas para pegar as infos necessárias
            $transaction = Transaction::where('id', $payment['transaction_id'])->get()->first();
            $origin_account = Account::where('id', $transaction['origin_account_id'])->get()->first();
            $destiny_account = Account::where('id', $transaction['destiny_account_id'])->get()->first();

            if (($origin_account['type'] == 'Cartão de crédito') || ($destiny_account['type'] == 'Cartão de crédito'))
            {
                return false;
            }
            if (!$destiny_account)
            {
                self::makePayment($payment->amount, $origin_account, $transaction->type);
                $payment->update([
                    'status' => 'done'
                ]);
            }
            self::makeTransfer($origin_account, $destiny_account, $payment->amount);
            $payment->update([
                'status' => 'done'
            ]);
        }
        return true;
    }

    private static function makePayment($amount, $account, $type)
    {
        if ($type == 'income')
        {
            $account->update([
                'balance' => $account->balance + $amount
            ]);
        }
        if ($type == 'outcome')
        {
            $account->update([
                'balance' => $account->balance - $amount
            ]);
        }
        return true;
    }

    private static function makeTransfer($origin_account, $destiny_account, $amount)
    {
        $origin_account->update([
            'balance' => $origin_account->balance - $amount
        ]);
        $destiny_account->update([
            'balance' => $destiny_account->balance + $amount
        ]);
        return true;
    }

}
