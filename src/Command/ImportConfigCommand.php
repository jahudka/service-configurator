<?php

declare(strict_types=1);

namespace M3J\Command;

use M3J\Helper\Client;
use M3J\Helper\ConfigInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ImportConfigCommand extends Command
{
  private ConfigInstaller $installer;
  private Client $client;

  public function __construct(ConfigInstaller $installer, Client $client)
  {
    parent::__construct();
    $this->installer = $installer;
    $this->client = $client;
  }

  protected function configure() : void
  {
    $this->setName('import:config')
      ->setAliases(['import'])
      ->setDescription('Import a config file')
      ->addArgument('service', InputArgument::REQUIRED, 'The name of the service the config belongs to')
      ->addArgument('file', InputArgument::REQUIRED, 'The path to the config file')
      ->addArgument('signature', InputArgument::OPTIONAL, 'The config signature; will be read from stdin if omitted');
  }

  protected function execute(InputInterface $input, OutputInterface $output) : int
  {
    $service = $input->getArgument('service');
    $file = $input->getArgument('file');

    if (!($signature = $input->getArgument('signature')) && !($signature = stream_get_contents(STDIN))) {
      $output->writeln('Signature must be specified as an argument or piped to stdin');
      return 1;
    }

    if (posix_geteuid() !== 0) {
      $this->client->import($service, $file, $signature);
    } else {
      $this->installer->import($service, $file, $signature);
    }

    return 0;
  }
}
