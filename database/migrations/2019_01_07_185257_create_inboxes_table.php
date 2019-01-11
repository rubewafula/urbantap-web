<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInboxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inboxes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->mediumInteger('msisdn');
            $table->enum('network', ['SAFARICOM', 'AIRTEL', 'TELKOM', 'EQUITEL', 'ORANGE', 'JTL'])->nullable();
            $table->string('short_code', 10)->nullable();
            $table->string('link_id', 256)->nullable();
            $table->string('message', 200);
            $table->integer('status_id');
            $table->timestamps();
            $table->foreign('user_id', 'inboxes_users_fk')->references('id')->on('users');
             $table->foreign('status_id', 'inboxes_statuses_fk')->references('id')->on('statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inboxes');
    }
}
