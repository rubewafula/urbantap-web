<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('service_provider_id');
            $table->unsignedInteger('provider_service_id');
            $table->integer('rating');
            $table->string('review', 400);
            $table->integer('status_id');
            $table->timestamp('deleted_at');

            $table->timestamps();

            $table->foreign('user_id','reviews_users_fk')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_provider_id', 'reviews_service_providers_fk')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('provider_service_id', 'reviews_provider_services_fk')->references('id')->on('provider_services')->onDelete('cascade');

            $table->foreign('status_id', 'reviews_statuses_fk')->references('id')->on('statuses');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
