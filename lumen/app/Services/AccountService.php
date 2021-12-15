<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Color;

class AccountService
{
    public static function insertAccount($request)
    {
        if (Account::where([
            'name' => $request['name'],
            'type' => $request['type'],
            'user_id' => $request->user()['id']
        ])->exists()) {
            return null;
        }
        $account = new Account;
        $account->user_id = $request->user()['id'];
        $account->name = $request->name;
        $account->type = $request->type;
        $account->balance = $request->balance;
        $account->color_id = $request->color_id;
        $account->invoice_closing_date = $request->invoice_closing_date;
        $account->invoice_due_date = $request->invoice_due_date;
        $account->save();
        return $account;
    }

    public static function deleteAccount($id)
    {
        if (!Account::where('id', $id)->exists()) {
            return null;
        }
        Account::where('id', $id)->update([
            'active' => false
        ]);
        return true;
    }

    public static function updateAccount($data, $account_id)
    {
        if (!Account::where('id', $account_id)->exists()) {
            return null;
        }

        $account = Account::where('id', $account_id)->first();

        $account->update(
            $data
        );

        return $account;
    }

    public static function getAccounts($user_id)
    {
        $response = [];
        $accounts = Account::where('user_id', $user_id)->get();
        foreach ($accounts as $account) {
            $response_array['id'] = $account['id'];
            $response_array['name'] = $account['name'];
            $response_array['type'] = $account['type'];
            $response_array['color'] = Color::where('id', $account['color_id'])->get()->first();
            $response_array['balance'] = $account['balance'];
            $response_array['invoice_closing_date'] = $account['invoice_closing_date'];
            $response_array['invoice_due_date'] = $account['invoice_due_date'];
            $response_array['active'] = ($account['active'] == 1) ? (true) : (false);

            array_push($response, $response_array);
        }
        return $response;
    }
}
