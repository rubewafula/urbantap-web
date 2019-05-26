<?php

namespace App\Utilities;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;
use \RuntimeException;
use \ErrorException;

class RabbitMQ
{
    
    protected $connection;

    public function __construct(){
    	$this->setConnection();
    }

    public function __destruct(){
    	if($this->connection != null){
            $this->connection->close();
        }
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

   public function publish(array $message, string $queue, 
       string $exchange, string $route=null){

	    if(is_null($this->connection)){
	    	$this->setConnection();
	    }
	    $channel = null;

	    if(!is_null($this->connection)){
	    	 try {
	    	 	
	    	 	 $channel = $this->connection->channel();
		    	 $dataInJSON = json_encode($message);
		    	 $msg = new AMQPMessage($dataInJSON);
                         Log::info("PUBLISHING MESSAGE Q => " .$queue. " echange => ". $exchange . " Route => ". $route) ;
                         if($route != null) {
		    	     $publishResult = $channel->basic_publish($msg, $exchange, $route);
                         } else {
		    	     $publishResult = $channel->basic_publish($msg, $exchange, $queue);
                         }
                         Log::info("Publish Result ==> $publishResult");
		    	 return true;

	    	 } catch (Exception $e) {
	    	 	 Log::error($e->getMessage());
	    	 }finally {
	    	 	if(!is_null($channel)){
			    	$channel->close();
	    	 	}
		}
	    	
	    }

	    return false;	

	}
}
