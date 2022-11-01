<?php

declare(strict_types=1);

namespace M3J\Command;

use M3J\Helper\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class QuitCommand extends Command
{
  private Client $client;

  public function __construct(Client $client)
  {
    parent::__construct();
    $this->client = $client;
  }

  protected function configure() : void
  {
    $this->setName('quit')
      ->setDescription('Stop the daemon');
  }

  protected function execute(InputInterface $input, OutputInterface $output) : int
  {
    $this->client->quit();
    return 0;
  }
}
