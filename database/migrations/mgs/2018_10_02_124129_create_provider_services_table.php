<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_provider_id');
            $table->unsignedInteger('service_id');
            $table->string('description', 400)->nullable();
            $table->double('cost', 10,2);
            $table->integer('duration_hours')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('status_id');

            $table->foreign('service_id','provider_services_services_fk')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('service_provider_id', 'provider_services_service_providers_fk')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('status_id', 'provider_service_statuses_fk')->references('id')->on('statuses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_services');
    }
}
