<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_trails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id');
            $table->integer('status_id');
            $table->integer('description');
            $table->timestamps();

            $table->foreign('status_id', 'booking_trails_statuses_fk')->references('id')->on('statuses');
            $table->foreign('booking_id', 'booking_trails_bookings_fk')->references('id')->on('bookings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_trails');
    }
}
