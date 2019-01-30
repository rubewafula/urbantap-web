<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfoliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_provider_id');
            /**
            JSON Portifolio image field - requires mysql 5.6 ++
            {
                "media_url":"", 
                "media_type":"audio*|video*|image*", 
                "size":""
            }
            **/
            $table->json('media_data')->nullable();
            $table->string('description')->nullable();
            $table->integer('status_id');
            $table->timestamp('deleted_at');
            $table->timestamps();

            $table->foreign('service_provider_id', 'portfolios_service_providers_fk')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('status_id', 'portfolios_statuses_fk2')->references('id')->on('statuses');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portfolios');
    }
}
