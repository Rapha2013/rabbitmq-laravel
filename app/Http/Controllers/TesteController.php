<?php

namespace App\Http\Controllers;

use App\Models\retorno;
use Illuminate\Http\Request;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TesteController extends Controller
{
    public function send()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('logs', 'fanout', false, false, false);

        $data = "info: Hello World!";
        
        $msg = new AMQPMessage($data);

        $channel->basic_publish($msg, 'logs');

        echo ' [x] Sent ', $data, "\n";

        $channel->close();
        $connection->close();
    }

    public function index()
    {

        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('usuarios', false, false, false, false);

        $callback = function ($msg) {
            retorno::create([
                'json' => json_encode($msg->body)
            ]);
        };

        $channel->basic_consume('usuarios', '', false, true, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
