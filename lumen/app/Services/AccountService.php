<?php


namespace App\Services;

use App\Models\Account;

class AccountService
{
    public static function insertAccount($request)
    {
        if(Account::where('name', $request->name)->exists())
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
            'invoice_closing_date' => $request->all()['invoice_closing_date'],
            'invoice_due_date' => $request->all()['invoice_due_date']
        ]);
        return true;
    }

    public static function getAccounts()
    {
        return Account::all();
    }

}