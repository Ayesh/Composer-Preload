<?php


namespace Ayesh\ComposerPreload\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PreloadCommand extends BaseCommand {
  protected function configure() {
    $this->setName('preload');
    $this->setDescription('Preloads the source files to PHP OPCache to speed up execution.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getComposer()->getPackage();
  }
}
