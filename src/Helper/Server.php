<?php

declare(strict_types=1);

namespace M3J\Helper;


class Server
{
  use ConnectionTrait;

  private ConfigInstaller $installer;
  private string $socketPath;

  public function __construct(ConfigInstaller $installer, string $socketPath)
  {
    $this->installer = $installer;
    $this->socketPath = $socketPath;
  }

  public function run() : void
  {
    @unlink($this->socketPath);
    @mkdir(dirname($this->socketPath), 0755, true);
    $sock = @stream_socket_server(sprintf('unix://%s', $this->socketPath), $errNo, $errStr);

    if ($sock === false) {
      throw new \RuntimeException($errStr ?? 'Failed to create server socket', $errNo ?? 0);
    }

    chmod($this->socketPath, 0644);

    while (($conn = stream_socket_accept($sock, -1)) !== false) {
      if (!$this->handleConnection($conn)) {
        break;
      }
    }

    @unlink($this->socketPath);
  }

  private function handleConnection($conn) : bool
  {
    try {
      $msg = $this->receiveMessage($conn);
    } catch (\Throwable) {
      $this->closeConnection($conn, 'invalid message');
      return true;
    }

    if ($msg === 'quit') {
      $this->closeConnection($conn);
      return false;
    } else if (!is_array($msg) || !isset($msg['service']) || !isset($msg['file']) || !isset($msg['signature'])) {
      $this->closeConnection($conn, 'invalid message');
      return true;
    }

    try {
      $this->installer->import($msg['service'], $msg['file'], $msg['signature']);
    } catch (\Throwable $e) {
      $this->closeConnection($conn, $e->getMessage() ?: 'error importing config file');
      return true;
    }

    try {
      $this->sendMessage($conn, ['message' => 'OK']);
    } catch(\Throwable) {}

    $this->closeConnection($conn);
    return true;
  }
}
