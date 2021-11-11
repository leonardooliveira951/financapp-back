<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Payment;
use Carbon\Carbon;

class PaymentService
{
    public static function insertPayments($transaction)
    {

        $account = Account::where('id', $transaction->origin_account_id)->get()->first();

        if ($account->type == 'Cartão de Crédito')
        {
            $amount = $transaction->amount / $transaction->installment;
        }

        self::default_payment($transaction, $account);

        dd('passou');

        return $payment;
    }

    public static function default_payment($transaction, $account)
    {
        for ($x = 0; $x < $transaction->installment; $x++) {
            $timestamp = strtotime("+{$x} month", strtotime($transaction->date));
            $payment_date = date("Y/m/d", $timestamp);
            $amount = $transaction->amount / $transaction->installment;
            $transaction_id = $transaction->id;

            $payment = self::schedule_payment($payment_date, $amount, $transaction_id);

            $current_date = Carbon::now()->timestamp;
            if (strtotime($payment_date) <= $current_date)
            {
                $account->update([
                    'balance' => $account->balance - $amount
                ]);
//                $payment->update([
//                    'status' => 'paid'
//                ]); TODO quando inserir a coluna status pela migration, descomentar
            }
        }
        dd('opa, cheguei ao fim');

        return true;
    }

    public static function schedule_payment($payment_date, $amount, $transaction_id)
    {
        $payment = new Payment();
        $payment->amount = $amount;
        $payment->date = $payment_date;
        $payment->transaction_id = $transaction_id;
//        $payment->status = 'scheduled'; TODO quando inserir a coluna status pela migration, descomentar
        $payment->save();
        return $payment;
    }
}
