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

        $payment = new Payment();
        $payment->amount = $transaction->amount;
        $payment->date = $transaction->date;
        $payment->transaction_id = $transaction->id;
        $payment->save();

        $current_date = Carbon::now()->toDateString();
        if ($current_date == $transaction->date)
        {
            $account->update([
                'balance' => $account->balance - $payment->amount
            ]);
            dd('fez o update');
        }

        dd('passou');

        return $payment;
    }





    public static function deleteCategory($id)
    {
        if(!Category::where('id',$id)->exists())
        {
            return null;
        }
        Category::where('id',$id)->update([
            'active' => false
        ]);
        return true;
    }

    public static function updateCategory($request)
    {
        if(!Category::where('id',$request->id)->exists())
        {
            return null;
        }
        Category::where('id',$request->id)->update([
            'name' => $request->all()['name'],
            'type' => $request->all()['type'],
            'color_id' => $request->all()['color_id']
        ]);
        return true;
    }

    public static function getCategories()
    {
        return Category::all();
    }

}
