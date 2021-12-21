<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public static function insertPayments($transaction)
    {
        $origin_account = Account::where('id', $transaction->origin_account_id)->get()->first();
        $destiny_account = Account::where('id', $transaction->destiny_account_id)->get()->first();

        if (!$destiny_account) {
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
            $installment = $x + 1;

            if ($account->type == 'credit_card') {
                $due_date = (new Carbon($transaction->invoice_first_charge))->day($account->invoice_due_date);
                $due_date = strtotime("+{$x} month", strtotime($due_date));
                $due_date = date("Y/m/d", $due_date);
                $invoice = InvoiceService::handleInvoice($due_date, $account->id, $amount);
                self::schedulePayment($payment_date, $amount, $transaction_id, $installment, $invoice->id);
                continue;
            }
            $payment = self::schedulePayment($payment_date, $amount, $transaction_id, $installment);

            $current_date = Carbon::now()->timestamp;
            if ((strtotime($payment_date) <= $current_date) && ($account->type != 'credit_card')) {
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
        if (strtotime($transaction->date) <= $current_date) {
            self::makeTransfer($origin_account, $destiny_account, $payment->amount);
            $payment->update([
                'status' => 'done'
            ]);
        }
        return true;
    }

    public static function schedulePayment($payment_date, $amount, $transaction_id, $installment, $invoice_id = NULL)
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
            ->where('status', 'scheduled')
            ->where('invoice_id', null)
            ->get();

        foreach ($payments as $payment) {
            // TODO aqui é interessante fazer um JOIN entre as tabelas para pegar as infos necessárias
            $transaction = Transaction::where('id', $payment['transaction_id'])->get()->first();
            $origin_account = Account::where('id', $transaction['origin_account_id'])->get()->first();
            $destiny_account = Account::where('id', $transaction['destiny_account_id'])->get()->first();

            if (($origin_account['type'] == 'credit_card') || ($destiny_account['type'] == 'credit_card')) {
                return false;
            }
            if (!$destiny_account) {
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
        if ($type == 'income') {
            $account->update([
                'balance' => $account->balance + $amount
            ]);
        }
        if ($type == 'outcome') {
            $account->update([
                'balance' => $account->balance - $amount
            ]);
        }
        return true;
    }

    public static function reversePayment($amount, $account, $type)
    {
        if ($type == 'income') {
            $account->update([
                'balance' => $account->balance - $amount
            ]);
        }
        if ($type == 'outcome') {
            $account->update([
                'balance' => $account->balance + $amount
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

    public static function reverseTransfer($origin_account, $destiny_account, $amount)
    {
        $origin_account->update([
            'balance' => $origin_account->balance + $amount
        ]);
        $destiny_account->update([
            'balance' => $destiny_account->balance - $amount
        ]);
        return true;
    }

    public static function getSummaryByDate($request)
    {
        $response = [];
        $outcome_total_amount = 0;
        $income_total_amount = 0;

        $month = date("m", strtotime($request->period));
        $year = date("Y", strtotime($request->period));

        $payments = DB::table('payments')
            ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->select('transactions.type',
                'transactions.category_id',
                'payments.*')
            ->whereRaw('transactions.user_id = ' . $request->user()['id'])
            ->whereRaw('MONTH(payments.date) = ' . $month)
            ->whereRaw('YEAR(payments.date) = ' . $year)
            ->get();

        foreach ($payments as $payment) {
//            if ($payment->category_id == CategoryService::CREDIT_CARD_PAYMENT_CATEGORY){
//                continue;
//            }

            switch ($payment->type) {
                case "outcome":
                    $outcome_total_amount += $payment->amount;
                    break;
                case "income":
                    $income_total_amount += $payment->amount;
                    break;
            }
        }

        $balance = $income_total_amount - $outcome_total_amount;

        $response['period_incomes'] = $income_total_amount;
        $response['period_outcomes'] = $outcome_total_amount;
        $response['period_balance'] = $balance;

        return $response;
    }

    public static function updatePaymentByTransaction($data, $account, $type , $destiny_account = null)
    {
        $payment = Payment::where('id', $data['payment_id'])->first();
        $amount_diff = (isset($data['amount']) ? ($data['amount'] - $payment->amount) : 0);

        $payment_update['amount'] = ($data['amount'] ?? $payment->amount);
        $payment_update['date'] = ($data['date'] ?? $payment->date);
        if ($payment->status == "done"){
            switch ($type) {
                case "income":
                    $account->update([
                        'balance' => $account->balance += $amount_diff
                    ]);
                    break;
                case "outcome":
                    $account->update([
                        'balance' => $account->balance -= $amount_diff
                    ]);
                    break;
                case "transfer":
                    $account->update([
                        'balance' => $account->balance -= $amount_diff
                    ]);
                    $destiny_account->update([
                        'balance' => $destiny_account->balance += $amount_diff
                    ]);
                    break;
            }
        }
        $payment->update($payment_update);
        return $amount_diff;

    }

}
