<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PaymentService;


class TransactionService
{
    public static function insertTransaction($request)
    {
        $transaction = new Transaction();
        $transaction->user_id = $request->user()['id'];
        $transaction->description = $request['description'];
        $transaction->type = $request['type'];
        $transaction->amount = $request['amount'];
        $transaction->date = $request['date'];
        $transaction->installments = $request['installments'];
        $transaction->category_id = $request['category_id'];
        $transaction->origin_account_id = $request['origin_account_id'];
        $transaction->destiny_account_id = $request['destiny_account_id'];
//        $invoice_first_charge = strtotime($request['invoice_first_charge']);
//        $transaction->invoice_first_charge = date("Y/m", $invoice_first_charge);
        $transaction->invoice_first_charge = $request['invoice_first_charge'];
        $transaction->save();

        PaymentService::insertPayments($transaction);

        return $transaction;
    }

    public static function updateTransaction($request)
    {
        if(!Transaction::where('id',$request->id)->exists())
        {
            return null;
        }
        Transaction::where('id',$request->id)->update([
            'description' => $request->all()['description'],
            'category_id' => $request->all()['category_id'],
            'amount' => $request->all()['amount'],
            'date' => $request->all()['date']
        ]);
        // TODO updatePayment() -> precisa verificar se já foi pago e alterar a data de todos os lançamentos referentes a essa transação
        return true;
    }

    public static function deleteTransaction($id)
    {
        if(!Transaction::where('id',$id)->exists())
        {
            return null;
        }
        return Transaction::where('id',$id)->delete();
    }

    public static function getTransactionByDate($period)
    {
        $response = [];

        $month = date("m", strtotime($period));
        $year = date("Y", strtotime($period));

        $payments = Payment::whereRaw(
            'MONTH(date) = ' . $month)
            ->whereRaw('YEAR(date) = ' . $year)
            ->get();

        foreach ($payments as $payment){
            $transaction = Transaction::where('id', $payment->transaction_id)->get()->first();

            $response_array['transaction_id'] = $transaction->id;
            $response_array['description'] = $transaction->description;
            $response_array['category'] = Category::where('id', $transaction->category_id)->get()->first();
            $response_array['account'] = Account::where('id', $transaction->origin_account_id)->get()->first();
            $response_array['date'] = $payment->date;
            $response_array['amount'] = $payment->amount;
            $response_array['installment'] = $payment->installment . "/" . $transaction->installments;
            $response_array['type'] = $transaction->type;

            array_push($response, $response_array);
        }
        return $response;
    }

}
