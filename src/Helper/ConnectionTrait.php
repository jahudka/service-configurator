<?php

declare(strict_types=1);

namespace M3J\Helper;


trait ConnectionTrait
{
  private function receiveMessage($conn) : mixed
  {
    return json_decode(fgets($conn), true, flags: JSON_THROW_ON_ERROR);
  }

  private function sendMessage($conn, mixed $payload) : void
  {
    fwrite($conn, json_encode($payload) . "\n");
  }

  private function closeConnection($conn, ?string $error = null) : void
  {
    if ($error) {
      try {
        $this->sendMessage($conn, ['error' => true, 'message' => $error]);
      } catch (\Throwable) {}
    }

    fclose($conn);
  }
}
