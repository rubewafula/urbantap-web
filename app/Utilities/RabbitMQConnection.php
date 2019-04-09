<?php

namespace App\Utilities;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnection
{
    
    public function getConnection(){

    	$connection = new AMQPStreamConnection(env("RABBITMQ_HOST"), env("RABBITMQ_PORT"),
    	 env("RABBITMQ_USER"), env("RABBITMQ_PASSWORD"));

    	return $connection;
    }
}