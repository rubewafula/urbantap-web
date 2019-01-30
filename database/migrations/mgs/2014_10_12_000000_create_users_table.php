<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->unsignedInteger('user_group')->default(100);
            $table->mediumInteger('phone_no');
            $table->string('email', 200)->unique();
            $table->string('password', 256);
            $table->integer('status_id');

            $table->rememberToken();
            $table->timestamps();

             $table->foreign('status_id', 'users_status_fk')->references('id')->on('statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
