<?php


namespace Ayesh\ComposerPreload\Composer\Command;


use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PreloadClearCommand extends BaseCommand{
  protected function configure() {
    $this->setName('preload:clear');
    $this->setDescription('Clear project-specific opcache entries or the global opcache.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
  }
}
