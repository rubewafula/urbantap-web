<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerficationfields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users',function(Blueprint  $table){

            $table->tinyinteger('verification_sends')->default(0);
            $table->tinyinteger('verification_tries')->default(0);
            $table->dateTime('verified_time')->nullable();
            $table->dateTime('verification_expiry_time')->nullable();





        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::table('users',function(Blueprint  $table){
            $table->dropColumn('verification_sends');
            $table->dropColumn('verification_tries');
            $table->dropColumn('verified_time');
            $table->dropColumn('verification_expiry_time');
           });
    }
}
