<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Payment;
use Carbon\Carbon;

class PaymentService
{
    public static function insertPayments($transaction)
    {

        $origin_account = Account::where('id', $transaction->origin_account_id)->get()->first();
        $destiny_account = Account::where('id', $transaction->destiny_account_id)->get()->first();

        if (!$destiny_account)
        {
            $payment = self::do_payment($transaction, $origin_account);
            return $payment;
        }

        $payment = self::do_transfer($transaction, $origin_account, $destiny_account);

        return $payment;
    }

    public static function do_payment($transaction, $account)
    {
        for ($x = 0; $x < $transaction->installments; $x++) {
            $timestamp = strtotime("+{$x} month", strtotime($transaction->date));
            $payment_date = date("Y/m/d", $timestamp);
            $amount = $transaction->amount / $transaction->installments;
            $transaction_id = $transaction->id;
            $installment = $x + 1;

            $payment = self::schedule_payment($payment_date, $amount, $transaction_id, $installment);

            $current_date = Carbon::now()->timestamp;
            if ((strtotime($payment_date) <= $current_date) && ($account->type != 'Cartão de crédito'))
            {
                if ($transaction->type == 'incoming')
                {
                    $account->update([
                        'balance' => $account->balance + $amount
                    ]);
                }
                if ($transaction->type == 'outcoming')
                {
                    $account->update([
                        'balance' => $account->balance - $amount
                    ]);

                }
                $payment->update([
                    'status' => 'done'
                ]);
            }
        }
        return true;
    }

    public static function do_transfer($transaction, $origin_account, $destiny_account)
    {
        $payment = new Payment();
        $payment->amount = $transaction->amount;
        $payment->date = $transaction->date;
        $payment->transaction_id = $transaction->id;
        $payment->save();

        $current_date = Carbon::now()->timestamp;
        if (strtotime($transaction->date) <= $current_date)
        {
            $origin_account->update([
                'balance' => $origin_account->balance - $payment->amount
            ]);
            $destiny_account->update([
                'balance' => $destiny_account->balance + $payment->amount
            ]);

            $payment->update([
                'status' => 'done'
            ]);
        }
        return true;
    }

    public static function schedule_payment($payment_date, $amount, $transaction_id, $installment)
    {
        $payment = new Payment();
        $payment->amount = $amount;
        $payment->date = $payment_date;
        $payment->transaction_id = $transaction_id;
        $payment->installment = $installment;
        $payment->save();
        return $payment;
    }
}
