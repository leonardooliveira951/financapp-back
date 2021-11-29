<?php


namespace App\Services;

use App\Models\Account;
use App\Models\Color;

class AccountService
{
    public static function insertAccount($request)
    {
        if(Account::where([
            'name' => $request['name'],
            'type' => $request['type'], 
            'user_id' => $request->user()['id']
        ])->exists())
        {
            return null;
        }
        $account = new Account;
        $account->user_id = $request->user()['id'];
        $account->name = $request->name;
        $account->type = $request->type;
        $account->balance = $request->balance;
        $account->color_id = $request->color_id;
        $account->limit = $request->limit;
        $account->invoice_closing_date = $request->invoice_closing_date;
        $account->invoice_due_date = $request->invoice_due_date;
        $account->save();
        return $account;
    }

    public static function deleteAccount($id)
    {
        if(!Account::where('id',$id)->exists())
        {
            return null;
        }
        Account::where('id',$id)->update([
            'active' => false
        ]);
        return true;
    }

    public static function updateAccount($request)
    {
        if(!Account::where('id',$request->id)->exists())
        {
            return null;
        }
        Account::where('id',$request->id)->update([
            'name' => $request->all()['name'],
            'type' => $request->all()['type'],
            'color_id' => $request->all()['color_id'],
            'balance' => $request->all()['balance'],
            'limit' => $request->all()['limit'],
            'invoice_closing_date' => $request->all()['invoice_closing_date'],
            'invoice_due_date' => $request->all()['invoice_due_date'],
            'active' => $request->all()['active']
        ]);
        return true;
    }

    public static function getAccounts($user_id)
    {
        $response = [];
        $accounts= Account::where('user_id', $user_id)->get();
        foreach ($accounts as $account)
        {
            $response_array['id'] = $account['id'];
            $response_array['name'] = $account['name'];
            $response_array['type'] = $account['type'];
            $response_array['color'] = Color::where('id', $account['color_id'])->get()->first();
            $response_array['balance'] = $account['balance'];
            $response_array['limit'] = $account['limit'];
            $response_array['invoice_closing_date'] = $account['invoice_closing_date'];
            $response_array['invoice_due_date'] = $account['invoice_due_date'];
            $response_array['active'] = ($account['active'] == 1) ? (true) : (false);

            array_push($response, $response_array);
        }
        return $response;
    }
}
