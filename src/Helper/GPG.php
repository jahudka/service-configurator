<?php

declare(strict_types=1);

namespace M3J\Helper;

use Symfony\Component\Process\Process;


class GPG
{
  private string $homeDir;
  private string $gpgPath;

  public function __construct(string $homeDir, string $gpgPath = 'gpg') {
    $this->homeDir = $homeDir;
    $this->gpgPath = $gpgPath;
  }

  public function importKey(string $key) : void
  {
    if ($this->isArmoredBlock($key, 'public key block')) {
      $this
        ->createProcess('--import', '-')
        ->setInput($key)
        ->mustRun();
    } else if (is_file($key)) {
      $this
        ->createProcess('--import', $key)
        ->mustRun();
    } else {
      throw new \InvalidArgumentException('Invalid GPG key');
    }
  }

  public function verify(string $filePath, string $signature) : bool
  {
    if ($this->isArmoredBlock($signature, 'signature')) {
      return $this
        ->createProcess('--verify', '-', $filePath)
        ->setInput($signature)
        ->run() === 0;
    } else if (is_file($signature)) {
      return $this
        ->createProcess('--verify', $signature, $filePath)
        ->run() === 0;
    } else {
      throw new \InvalidArgumentException('Invalid signature argument');
    }
  }

  private function isArmoredBlock(string $text, string $type) : bool
  {
    $pattern = sprintf('~^-+\s*begin\s+(?:gpg|pgp)\s+%s\s*-+\r?\n~i', strtr($type, [' ' => '\s+']));
    return (bool) preg_match($pattern, $text);
  }

  private function createProcess(string ... $args) : Process
  {
    @mkdir($this->homeDir, 0700, true);

    return new Process([
      $this->gpgPath,
      '--homedir',
      $this->homeDir,
      ...$args,
    ]);
  }
}
