<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;


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
        $transaction->invoice_first_charge = $request['invoice_first_charge'];
        $transaction->save();

        PaymentService::insertPayments($transaction);

        return $transaction;
    }

    public static function updateTransaction($data, $transaction_id)
    {
        if(!Transaction::where('id',$transaction_id)->exists())
        {
            return null;
        }

        $transaction = Transaction::where('id',$transaction_id)->first();

        $transaction->update(
            $data
        );
        // TODO updatePayment() -> precisa verificar se já foi pago e alterar a data de todos os lançamentos referentes a essa transação
        return $transaction;
    }

    public static function deleteTransaction($id)
    {
        if(!Transaction::where('id',$id)->exists())
        {
            return null;
        }
        return Transaction::where('id',$id)->delete();
    }

    public static function getTransactionByDate($request)
    {
        $response = [];

        $month = date("m", strtotime($request->period));
        $year = date("Y", strtotime($request->period));


        $payments = DB::table('payments')
            ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->select('transactions.*', 'payments.*')
            ->whereRaw('transactions.user_id = ' . $request->user()['id'])
            ->whereRaw('MONTH(payments.date) = ' . $month)
            ->whereRaw('YEAR(payments.date) = ' . $year)
            ->get();

        foreach ($payments as $payment){
            $response_array['id'] = $payment->transaction_id;
            $response_array['description'] = $payment->description;
            $response_array['category'] = Category::where('id', $payment->category_id)->get()->first();
            $response_array['account'] = Account::where('id', $payment->origin_account_id)->get()->first();
            $response_array['date'] = $payment->date;
            $response_array['amount'] = $payment->amount;
            $response_array['installment'] = $payment->installment . "/" . $payment->installments;
            $response_array['type'] = $payment->type;

            array_push($response, $response_array);
        }
        return $response;
    }

    public static function insertInvoiceTransaction($invoice, $account, $amount, $payment_date)
    {
        $due_date = date("m/Y", strtotime($invoice->due_date));

        $transaction = new Transaction();
        $transaction->user_id = $account->user_id;
        $transaction->description = "Pagamento da fatura de {$due_date} do cartão {$account->name}";
        $transaction->type = "outcome";
        $transaction->amount = $amount;
        $transaction->date = $payment_date;
        $transaction->installments = 1;
        $transaction->category_id = CategoryService::CREDIT_CARD_PAYMENT_CATEGORY;
        $transaction->origin_account_id = $account->id;
        $transaction->destiny_account_id = NULL;
        $transaction->invoice_first_charge = NULL;
        $transaction->save();

        PaymentService::insertPayments($transaction);

        return $transaction;
    }
}
