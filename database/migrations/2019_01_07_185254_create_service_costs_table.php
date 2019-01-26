<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_costs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id');
            $table->float('base_cost', 10, 2);
            $table->integer('status_id');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('status_id', 'service_costs_statuses_fk')->references('id')->on('statuses');
            $table->foreign('service_id', 'service_costs_services_fk')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_costs');
    }
}
