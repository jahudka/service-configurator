<?php

declare(strict_types=1);

namespace M3J\DI;

use M3J\Command\ImportConfigCommand;
use M3J\Command\ImportKeyCommand;
use M3J\Command\QuitCommand;
use M3J\Command\RunCommand;
use M3J\Helper\Client;
use M3J\Helper\ConfigInstaller;
use M3J\Helper\GPG;
use M3J\Helper\Server;
use M3J\Helper\Service;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;


class Container
{
  private array $parameters;
  private array $services = [];

  public function __construct(array $parameters)
  {
    $this->parameters = $parameters;
  }

  public function getGpg() : GPG
  {
    return $this->services[GPG::class] ??= $this->createGpg();
  }

  public function getConfigInstaller() : ConfigInstaller
  {
    return $this->services[ConfigInstaller::class] ??= $this->createConfigInstaller();
  }

  public function getServer() : Server
  {
    return $this->services[Server::class] ??= $this->createServer();
  }

  public function getClient() : Client
  {
    return $this->services[Client::class] ??= $this->createClient();
  }

  public function getApplication() : Application
  {
    return $this->services[Application::class] ??= $this->createApplication();
  }

  private function createGpg() : GPG
  {
    return new GPG(...$this->parameters['gpg']);
  }

  private function createConfigInstaller() : ConfigInstaller
  {
    $installer = new ConfigInstaller($this->getGpg(), $this->parameters['tempDir']);
    $services = Yaml::parseFile($this->parameters['configPath']);

    foreach ($services as $service => $config) {
      $installer->addService($service, new Service(...$config));
    }

    return $installer;
  }

  private function createServer() : Server
  {
    return new Server($this->getConfigInstaller(), $this->parameters['tempDir'] . '/sc.sock');
  }

  private function createClient() : Client
  {
    return new Client($this->parameters['tempDir'] . '/sc.sock');
  }

  private function createImportConfigCommand() : ImportConfigCommand
  {
    return new ImportConfigCommand($this->getConfigInstaller(), $this->getClient());
  }

  private function createImportKeyCommand() : ImportKeyCommand
  {
    return new ImportKeyCommand($this->getGpg());
  }

  private function createRunCommand() : RunCommand
  {
    return new RunCommand($this->getServer());
  }

  private function createQuitCommand() : QuitCommand
  {
    return new QuitCommand($this->getClient());
  }

  private function createApplication() : Application
  {
    $application = new Application('Service Configurator', '1.0');
    $application->add($this->createImportConfigCommand());
    $application->add($this->createImportKeyCommand());
    $application->add($this->createRunCommand());
    $application->add($this->createQuitCommand());
    return $application;
  }
}
