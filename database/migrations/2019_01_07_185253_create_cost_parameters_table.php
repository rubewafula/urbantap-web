<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_parameters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id');
            $table->integer('status_id');
            $table->float('weight', 10,2);
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();

            $table->foreign('status_id', 'cost_parameters_statuses_fk')->references('id')->on('statuses');

            $table->foreign('status_id', 'cost_parameters_services_fk')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_parameters');
    }
}
