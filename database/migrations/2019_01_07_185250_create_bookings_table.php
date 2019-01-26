<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_service_id');
            $table->unsignedInteger('service_provider_id');
            $table->unsignedInteger('user_id');
            $table->datetime('booking_time');
            $table->datetime('booking_duration');
            $table->datetime('expiry_time')->nullable();
            $table->integer('status_id');
            $table->datetime('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('provider_service_id', 'bookings_provider_services_fk')->references('id')->on('provider_services')->onDelete('cascade');
            $table->foreign('service_provider_id', 'bookings_service_providers_fk')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('user_id', 'booking_users_fk')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_id', 'bookings_statuses_fk12')->references('id')->on('statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
