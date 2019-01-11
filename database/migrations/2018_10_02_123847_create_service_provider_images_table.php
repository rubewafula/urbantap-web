<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceProviderImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_provider_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_provider_id');
            $table->string('image_url', 200);
            $table->string('description')->nullable();
            $table->integer('status_id')->nullable();
            $table->timestamps();

            $table->foreign('service_provider_id', 'service_provider_images_service_providers_fk')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('statuses');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_provider_images');
    }
}
