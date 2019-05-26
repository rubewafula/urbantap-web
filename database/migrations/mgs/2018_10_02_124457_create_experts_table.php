<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_provider_id');
            $table->string('business_description', 400);
            $table->string('id_number', 12);
            $table->datetime('date_of_birth');
            $table->enum('gender', ['Male', 'Female', 'Un-disclosed']);
            $table->string('passport_photo', 200);
            $table->string('home_location', 45)->nullable();
            $table->string('work_phone_no', 12)->nullable();
            $table->string('work_location', 45)->nullable();
            $table->float('work_lat')->nullable();
            $table->float('work_lng')->nullable();

            $table->integer('status_id');
            $table->float('overall_rating',10,2)->nullable();
            $table->integer('overall_likes')->nullable();
            $table->integer('overall_dislikes')->nullable();

            $table->foreign('service_provider_id','experts_service_providers_fk')->references('id')->on('service_providers')->onDelete('cascade');

            $table->foreign('status_id', 'experts_statuses_fk')->references('id')->on('statuses');
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
        Schema::dropIfExists('experts');
    }
}
