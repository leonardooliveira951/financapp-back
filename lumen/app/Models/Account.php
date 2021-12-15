<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['balance', 'name', 'invoice_closing_date', 'invoice_due_date', 'color_id', 'active'];
}
