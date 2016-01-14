<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

// Make sure composer dependencies have been installed
require __DIR__ . '/../vendor/autoload.php';

/**
* chat.php
* Send any incoming messages to all connected clients (except sender)
*/
class Chat implements MessageComponentInterface {
  protected $rooms, $clients;

  public function __construct() {
    $this->rooms = array();
    $this->clients = new \SplObjectStorage;
  }

  public function onOpen(ConnectionInterface $conn) {
    // $this->clients->attach($conn);
  }

  public function onMessage(ConnectionInterface $from, $json) {
    $data = json_decode($json);
    if (!isset($data->id) || !isset($data->action)) {
      $from->send(json_encode(array(
        'status' => 'error',
        'message' => 'Invalid values.'
      )));
      return;
    }

    switch ($data->action) {
      case "join":
        foreach ($this->clients as $client) {
          if ($from == $client) {
            $from->send(json_encode(array(
              'status' => 'error',
              'message' => 'You already joined to any.'
            )));
            return;
          }
        }

        if (!isset($this->rooms[ $data->id ])) {
          $this->rooms[ $data->id ] = new \SplObjectStorage;
        }
        $this->rooms[ $data->id ]->attach($from);
        $this->clients->attach($from);

        $from->send(json_encode(array(
          'status' => 'success'
        )));
        break;
      case "post":
        foreach ($this->rooms as $id => $storage){
          if ($storage->contains($from) && $id === $data->id) {
            foreach ($storage as $client) {
              if ($from != $client) $client->send($json);
            }
            $from->send(json_encode(array(
              'status' => 'success'
            )));
            break;
          }
        }
        $from->send(json_encode(array(
          'status' => 'error',
          'message' => 'Invalid values.'
        )));
        break;
      default:
        $from->send(json_encode(array(
          'status' => 'error',
          'message' => 'Invalid action.'
        )));
        break;
    }
  }

  public function onClose(ConnectionInterface $conn) {
    foreach ($this->rooms as $id => $storage){
      if ($storage->contains($conn)) {
        $storage->detach($conn);
        foreach ($storage as $client) {
          $client->send(json_encode(array(
            'data' => array(
              'type' => 'users',
              'message' => $storage->count()
            )
          )));
        }
        if ($storage->count() <= 0) unset($this->rooms[$id]);
        break;
      }
    }
    $this->clients->detach($conn);
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    $conn->close();
  }
}

// Run the server application through the WebSocket protocol on port 8080
// $app = new Ratchet\App('192.168.1.72', 8080);
// $app->route('/chat', new MyChat);
// $app->route('/echo', new Ratchet\Server\EchoServer, array('*'));
// $app->run();
$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new Chat()
    )
  ),
  8080
);
$server->run();
