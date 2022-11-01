<?php

declare(strict_types=1);

namespace M3J\Command;

use M3J\Helper\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RunCommand extends Command
{
  private Server $server;

  public function __construct(Server $server)
  {
    parent::__construct();
    $this->server = $server;
  }


  protected function configure() : void
  {
    $this->setName('run');
  }

  protected function execute(InputInterface $input, OutputInterface $output) : int
  {
    $this->server->run();
    return 0;
  }
}
