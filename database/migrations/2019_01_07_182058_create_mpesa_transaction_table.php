<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMpesaTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();  
            $table->mediumInteger('msisdn');            
    	    $table->datetime('transaction_time');  
    	    $table->string('message',200);           
    	    $table->string('account_no', 45)->nullable();        
    	    $table->string('mpesa_code',25);        
    	    $table->double('amount', 10, 2);         
    	    $table->string('names', 100);      
    	    $table->string('paybill_no', 10);  
            $table->integer('status_id');
            $table->timestamps();
            
            $table->foreign('status_id', 'mpesa_transactions_statuses_fk')->references('id')->on('statuses');
            $table->foreign('user_ud', 'mpesa_transaction_users_fk')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_transactions');
    }
}
