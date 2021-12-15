<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['description', 'amount', 'installments', 'category_id', 'invoice_first_charge', 'date'];
}
