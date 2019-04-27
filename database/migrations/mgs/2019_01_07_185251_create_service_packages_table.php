<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id');
            $table->string('package_name', 45);
            $table->string('description', 200);
            $table->integer('status_id');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('service_id', 'service_packages_services_fk123')->references('id')->on('services');
            $table->foreign('status_id', 'service_packages_statuses_fK12')->references('id')->on('statuses');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_packages');
    }
}
