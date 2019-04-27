<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference', 256);
            $table->datetime('date_received');
            $table->integer('booking_id');
            $table->string('payment_method');
            $table->string('paid_by_name', 150);
            $table->string('paid_by_msisdn', 12);
            $table->float('amount', 10, 2);
            $table->float('received_payment', 10, 2);
            $table->float('balance', 10, 2);
            $table->integer('status_id');
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();

            $table->foreign('status_id', 'payments_statuses_fk')->references('id')->on('statuses');
            $table->foreign('booking_id', 'payments_bookings_fk')->references('id')->on('bookings');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
