<?php

declare(strict_types=1);

namespace M3J\Helper;


class ConfigInstaller
{
  private GPG $gpg;
  private string $tempDir;
  private array $services = [];

  public function __construct(GPG $gpg, string $tempDir)
  {
    $this->gpg = $gpg;
    $this->tempDir = $tempDir;
  }

  public function addService(string $name, Service $service) : void
  {
    $this->services[$name] = $service;
  }

  public function import(string $service, string $configPath, string $signature) : void
  {
    $service = $this->getService($service);
    $config = new ConfigFile($this->gpg, $this->tempDir, $configPath, $signature);

    try {
      $config->verifySignature();
      $service->install($config);
    } catch (\Throwable $e) {
      $config->cleanup();
      throw $e;
    }
  }

  private function getService(string $service) : Service
  {
    if (isset($this->services[$service])) {
      return $this->services[$service];
    }

    throw new \InvalidArgumentException(sprintf('Unknown service "%s"', $service));
  }
}
