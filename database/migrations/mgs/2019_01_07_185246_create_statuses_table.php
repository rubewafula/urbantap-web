<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('status_category_id');
            $table->string('status_code', 5);
            $table->string('description', 200)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

             $table->foreign('status_category_id', 'statuses_status_category_fk')->references('id')->on('status_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}
