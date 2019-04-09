<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference', 256);
            $table->string('provider_reference', 256)->nullable();
            $table->datetime('date_sent');
            $table->integer('user_id');
            $table->integer('msisdn', 12);
            $table->string('payment_method');
            $table->float('amount', 10, 2);
            $table->float('transaction_cost', 10, 2);
            $table->float('balance', 10, 2);
            $table->integer('status_id');
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();

            $table->foreign('status_id', 'payouts_statuses_fk')->references('id')->on('statuses');
            $table->foreign('user_id', 'payouts_users_fk')->references('id')->on('users');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payouts');
    }
}
