<?php

declare(strict_types=1);

namespace M3J\Helper;

use Symfony\Component\Process\Process;


class Service
{
  private string $configDir;
  private ?string $owner;
  private ?string $group;
  private ?int $mode;
  private ?string $checkCmd;
  private ?string $reloadCmd;

  public function __construct(
    string $configDir,
    ?string $owner = null,
    ?string $group = null,
    ?int $mode = null,
    ?string $checkCmd = null,
    ?string $reloadCmd = null,
  ) {
    $this->configDir = $configDir;
    $this->owner = $owner;
    $this->group = $group;
    $this->mode = $mode;
    $this->checkCmd = $checkCmd;
    $this->reloadCmd = $reloadCmd;
  }

  public function install(ConfigFile $configFile) : void
  {
    $dstPath = sprintf('%s/%s', $this->configDir, $configFile->getName());

    if ($configFile->isSameAsCurrent($dstPath)) {
      $configFile->cleanup();
      return;
    }

    $this->setOwnerAndPermissions($configFile);
    $this->backup($dstPath);

    @mkdir(dirname($dstPath), $this->mode ? $this->mode | 0111 : 0750, true);
    $configFile->install($dstPath);

    try {
      $this->runHook($this->checkCmd);
    } catch (\Throwable $e) {
      $this->restore($dstPath);
      throw $e;
    }

    try {
      $this->runHook($this->reloadCmd);
    } catch (\Throwable $e) {
      $this->restore($dstPath);

      try {
        $this->runHook($this->reloadCmd);
      } catch (\Throwable) {}

      throw $e;
    }

    $this->cleanup($dstPath);
  }

  private function setOwnerAndPermissions(ConfigFile $configFile) : void
  {
    if (isset($this->owner)) {
      $configFile->setOwner($this->owner);
    }

    if (isset($this->group)) {
      $configFile->setGroup($this->group);
    }

    if (isset($this->mode)) {
      $configFile->setPermissions($this->mode);
    }
  }

  private function backup(string $configFile) : void
  {
    if (is_file($configFile)) {
      $backupFile = $configFile . '.scbak';
      @unlink($backupFile);
      copy($configFile, $backupFile);
    }
  }

  private function restore(string $configFile) : void
  {
    $backupFile = $configFile . '.scbak';

    if (is_file($backupFile)) {
      rename($backupFile, $configFile);
    }
  }

  private function cleanup(string $configFile) : void
  {
    @unlink($configFile . '.scbak');
  }

  private function runHook(?string $cmd) : void
  {
    if ($cmd) {
      Process::fromShellCommandline($cmd)->mustRun();
    }
  }
}
