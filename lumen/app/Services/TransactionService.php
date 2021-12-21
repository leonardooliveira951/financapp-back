<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PaymentService;
use DateTime;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use IntlDateFormatter;


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
        if (!Transaction::where('id', $transaction_id)->exists()) {
            return null;
        }

        $transaction = Transaction::where('id', $transaction_id)->first();

        if (isset($data['amount']) || isset($data['date'])){
            PaymentService::updatePaymentByTransaction($data);
        }
        dd('coco');

        dd('tchau');
        $transaction->update(
            $data
        );
        return $transaction;
    }

    public static function deleteTransaction($id)
    {
        if (!Transaction::where('id', $id)->exists()) {
            return null;
        }
        $transaction = Transaction::where('id', $id)->first();
        $origin_account = Account::where('id', $transaction->origin_account_id)->first();
        $destiny_account = Account::where('id', $transaction->destiny_account_id)->first();
        $payments = Payment::where('transaction_id', $id)->get();


        if ($origin_account->type == 'credit_card') {
            return false;
        }
        foreach ($payments as $payment) {
            if ($payment->status == "done" && !$destiny_account) {
                PaymentService::reversePayment($payment->amount, $origin_account, $transaction->type);
            }
            if ($payment->status == "done" && $destiny_account) {
                PaymentService::reverseTransfer($origin_account, $destiny_account, $payment->amount);
            }
        }
        return Transaction::where('id', $id)->delete();
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

        foreach ($payments as $payment) {
            $response_array['id'] = $payment->transaction_id;
            $response_array['payment_id'] = $payment->id;
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

    public static function getDashboard($period, $user_id)
    {
        $month = date("m", strtotime($period));
        $year = date("Y", strtotime($period));
        $month_expenses = self::getMonthlyExpenses($user_id, $month, $year);

        $monthly_balance = self::getMonthlyBalance($user_id, $month, $year);

        $response["month"] = $month;
        $response["year"] = $year;
        $response["month_expenses"] = $month_expenses;
        $response["monthly_balance"] = $monthly_balance;

        return $response;
    }

    private static function getMonthlyExpenses($user_id, $month, $year)
    {
        return DB::table('categories')
            ->join('transactions', 'categories.id', '=', 'transactions.category_id')
            ->join('payments', 'transactions.id', '=', 'payments.transaction_id')
            ->join('colors', 'categories.color_id', '=', 'colors.id')
            ->select('categories.name AS name', DB::raw('SUM(payments.amount) AS amount'), DB::raw('MAX(colors.hex_code) AS color'))
            ->whereRaw('categories.user_id = ' . $user_id)
            ->whereRaw('categories.type = "outcome"')
            ->whereRaw('MONTH(payments.date) = ' . $month)
            ->whereRaw('YEAR(payments.date) = ' . $year)
            ->groupBy('name')
            ->get();
    }

    private static function getMonthlyBalance($user_id, $month, $year)
    {
        $response = [];
        for ($x = 5; $x >= 0; $x--) {
            $date_timestamp = strtotime("-{$x} month", strtotime($year . "/" . $month));
            $current_month = date("m", $date_timestamp);
            $current_year = date("Y", $date_timestamp);
            
            $month_income = self::getSumOfTotalByType($user_id, $current_month, $current_year, "income");
            $month_outcome = self::getSumOfTotalByType($user_id, $current_month, $current_year, "outcome");
            $month_balance['month'] = date("M", mktime(0, 0, 0, $current_month, 1, 2021));
            $month_balance['income'] = $month_income;
            $month_balance['outcome'] = $month_outcome;

            $response[] = $month_balance;
        }
        return $response;
    }

    private static function getSumOfTotalByType($user_id, $month, $year, $type)
    {
        $amount = DB::table('payments')
            ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->select(
                DB::raw('SUM(payments.amount) AS amount'))
            ->whereRaw('transactions.user_id = ' . $user_id)
            ->whereRaw('MONTH(payments.date) = ' . $month)
            ->whereRaw('YEAR(payments.date) = ' . $year)
            ->whereRaw("transactions.type = '$type'")
            ->groupBy('type')
            ->get()->first();
        if (!$amount) {
            return null;
        }
        return $amount->amount;
    }

}
