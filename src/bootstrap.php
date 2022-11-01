<?php

declare(strict_types=1);

use M3J\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

umask(0027);

return new Container([
  'tempDir' => __DIR__ . '/../var/run',
  'gpg' => [
    'homeDir' => __DIR__ . '/../var/gpg',
  ],
  'configPath' => __DIR__ . '/../etc/services.yaml',
]);
