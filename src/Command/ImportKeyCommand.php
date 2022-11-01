<?php

declare(strict_types=1);

namespace M3J\Command;

use M3J\Helper\GPG;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ImportKeyCommand extends Command
{
  private GPG $gpg;

  public function __construct(GPG $gpg)
  {
    parent::__construct();
    $this->gpg = $gpg;
  }

  protected function configure() : void
  {
    $this->setName('import:key')
      ->setDescription('Import GPG public key')
      ->addArgument('key', InputArgument::OPTIONAL, 'Path to public key file. If not provided, key will be read from stdin.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) : int
  {
    if (!($key = $input->getArgument('key')) && !($key = stream_get_contents(STDIN))) {
      $output->writeln('Key must be specified as an argument or piped to stdin');
      return 1;
    }

    $this->gpg->importKey($key);
    return 0;
  }
}
