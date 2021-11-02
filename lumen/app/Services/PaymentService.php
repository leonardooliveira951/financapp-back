<?php


namespace App\Services;

use App\Models\Payment;

class TransactionService
{
    public static function insertPayments($transaction)
    {
        if ($transaction->installment > 1){
            dd('implementar parcelados');
        }

        $payment = new Payment();
        $payment->amount = $transaction->amount;
        $payment->date = $transaction->date;
        $payment->transaction_id = $transaction->id;
        $payment->save();

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
