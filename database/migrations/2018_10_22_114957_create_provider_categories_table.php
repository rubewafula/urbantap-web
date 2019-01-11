<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_provider_id');
            $table->unsignedInteger('category_id');
            $table->integer('status_id');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('service_provider_id', 'provider_categories_service_providers_fk')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('category_id', 'provider_categories_categories_fk')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('status_id', 'provider_categories_statuses_fk')->references('id')->on('statuses');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_categories');
    }
}
