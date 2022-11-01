<?php

declare(strict_types=1);

namespace M3J\Helper;


class ConfigFile
{
  private GPG $gpg;
  private string $path;
  private string $name;
  private string $signature;
  private bool $installed = false;

  public function __construct(GPG $gpg, string $tempDir, string $srcPath, string $signature)
  {
    $this->gpg = $gpg;
    $this->path = $this->createTmpFile($tempDir, $srcPath);
    $this->name = basename($srcPath);
    $this->signature = $signature;
  }

  public function getName() : string
  {
    return $this->name;
  }

  public function isSameAsCurrent(string $configPath) : bool
  {
    return is_file($configPath) && md5_file($this->path) === md5_file($configPath);
  }

  public function verifySignature() : void
  {
    if (!$this->gpg->verify($this->path, $this->signature)) {
      throw new \InvalidArgumentException('Signature verification failed');
    }
  }

  public function setOwner(string $owner) : void
  {
    chown($this->path, $owner);
  }

  public function setGroup(string $group) : void
  {
    chgrp($this->path, $group);
  }

  public function setPermissions(int $permissions) : void
  {
    chmod($this->path, $permissions);
  }

  public function install(string $dstPath) : void
  {
    if (!$this->installed) {
      $this->installed = true;
      rename($this->path, $dstPath);
    }
  }

  public function cleanup() : void
  {
    if (!$this->installed) {
      @unlink($this->path);
    }
  }

  private function createTmpFile(string $tempDir, string $srcPath) : string
  {
    @mkdir($tempDir, 0755, true);
    $tmpFile = tempnam($tempDir, 'cfg');
    $src = fopen($srcPath, 'rb');
    $dst = fopen($tmpFile, 'wb');
    stream_copy_to_stream($src, $dst);
    fclose($src);
    fclose($dst);
    return $tmpFile;
  }

  public function __destroy() : void
  {
    $this->cleanup();
  }
}
