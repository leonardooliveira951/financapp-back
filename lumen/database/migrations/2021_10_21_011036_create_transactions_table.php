<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('description');
            $table->string('type');
            $table->double('amount');
            $table->smallInteger('installments')->default(1);
            $table->dateTime('date');
            $table->foreignId('category_id')->constrained();
            $table->unsignedBigInteger('origin_account_id');
            $table->unsignedBigInteger('destiny_account_id')->nullable();
            $table->dateTime('invoice_first_charge')->nullable();
            $table->foreign('origin_account_id')->references('id')->on('accounts');
            $table->foreign('destiny_account_id')->references('id')->on('accounts');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
