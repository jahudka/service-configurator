<?php

declare(strict_types=1);

namespace M3J\Helper;


class Client
{
  use ConnectionTrait;

  private string $socketPath;

  public function __construct(string $socketPath)
  {
    $this->socketPath = $socketPath;
  }

  public function import(string $service, string $file, string $signature) : void
  {
    $conn = $this->connect();

    $this->sendMessage($conn, [
      'service' => $service,
      'file' => $file,
      'signature' => $signature,
    ]);

    $result = $this->receiveMessage($conn);
    $this->closeConnection($conn);

    if (!empty($result['error'])) {
      throw new \RuntimeException($result['message'] ?? 'error');
    }
  }

  public function quit() : void
  {
    $conn = $this->connect();
    $this->sendMessage($conn, 'quit');
    $this->closeConnection($conn);
  }

  private function connect()
  {
    $conn = @stream_socket_client('unix://' . $this->socketPath, $errNo, $errStr);

    if ($conn === false) {
      throw new \RuntimeException($errStr ?? 'Failed to connect to service', $errNo ?? 0);
    }

    return $conn;
  }
}
