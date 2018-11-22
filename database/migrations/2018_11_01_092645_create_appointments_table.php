<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_services_id');
            $table->unsignedInteger('service_provider_id');
            $table->unsignedInteger('customer_id');
            $table->date('date');
            $table->time('time');
            $table->enum('status', ['BOOKED', 'ACCEPTED', 'CANCELLED'])->default('BOOKED');
            $table->timestamps();

            $table->foreign('provider_services_id')->references('id')->on('provider_services')->onDelete('cascade');
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
