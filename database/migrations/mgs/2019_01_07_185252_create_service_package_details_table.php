<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicePackageDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_package_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_detail_id');
            /**
            JSON Portifolio image field - requires mysql 5.6 ++
            {
                "media_url":"", 
                "media_type":"audio*|video*|image*", 
                "size":""
            }
            **/
            $table->json('media_data')->nullable();
            $table->integer('status_id');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('service_detail_id', 'service_package_details_service_packages_fk')->references('id')->on('service_packages');
            $table->foreign('status_id', 'service_package_details_statuses_fk')->references('id')->on('statuses');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_package_details');
    }
}
