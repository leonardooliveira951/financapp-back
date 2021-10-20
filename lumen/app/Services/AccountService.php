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
            'color' => $request->all()['color'],
            'active' => $request->all()['active']
        ]);
        return true;
    }

    public static function getCategories()
    {
        return Category::all();
    }

}
