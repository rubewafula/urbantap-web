<?php

namespace App\Utilities;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Exception\AMQPConnectionClosedException
use PhpAmqpLib\Exception\AMQPIOException
use PhpAmqpLib\Message\AMQPMessage;
use \RuntimeException
use \ErrorException

class RabbitMQConnection
{
    
    protected $connection;

    public function __construct(){
    	$this->setConnection();
    }

    public function getConnection(){

    	if(is_null($this->connection)){
    		$this->setConnection();	
    	}
    	return $this->connection;

    }

    public function setConnection(){

    	try {
	        $this->connection = new AMQPStreamConnection(env("RABBITMQ_HOST"), 
	        	env("RABBITMQ_PORT"),  env("RABBITMQ_USER"), env("RABBITMQ_PASSWORD"));
	        // Your application code goes here.
	    } catch(AMQPRuntimeException $e) {
	        Log::error($e->getMessage());
	        $this->connection = null;
	    } catch(\RuntimeException $e) {
	    	Log::error($e->getMessage());
	    	$this->connection = null;
	    } catch(\ErrorException $e) {
	    	Log::error($e->getMessage());
	    	$this->connection = null;
	    }

    	return $this->connection;
    }

   private function publish(array $message, string $exchange){

	    if(is_null($this->connection)){
	    	$this->setConnection();
	    }
	    $channel = null;

	    if(!is_null($this->connection)){
	    	 try {
	    	 	
	    	 	 $channel = $this->connection->channel();
		    	 $dataInJSON = json_encode($postData);
		    	 $msg = new AMQPMessage($postData);
		    	 $publishResult = $channel->basic_publish($msg, $exchange);
		    	 return true;

	    	 } catch (Exception $e) {
	    	 	 Log::error($e->getMessage());
	    	 }finally {
	    	 	if(!is_null($channel)){
			    	$channel->close();
	    	 	}
				$this->connection->close();
			}
	    	
	    }

	    return false;	

	}
}
