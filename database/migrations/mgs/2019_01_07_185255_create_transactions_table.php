<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->integer('user_id');
            $table->enum('transaction_type', ['DEBIT', 'CREDIT']);
            $table->string('reference', 70);
            $table->float('amount', 10,2);
            $table->float('running_balance', 10,2);
            $table->integer('status_id');
            $table->timestamp('deleted_at');
            $table->timestamps();

            $table->foreign('status_id', 'transactions_statuses_fk')->references('id')->on('statuses');
            $table->foreign('user_id', 'transactions_users_fk')->references('id')->on('users');
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
