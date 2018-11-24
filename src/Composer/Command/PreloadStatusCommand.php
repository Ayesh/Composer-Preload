<?php


namespace Ayesh\ComposerPreload\Composer\Command;


use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PreloadStatusCommand extends BaseCommand{
  protected function configure() {
    $this->setName('preload:status');
    $this->setDescription('Show status of the opcache');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = $this->getIO();
    $io->writeError('Preload file created successfully.');
  }
}
