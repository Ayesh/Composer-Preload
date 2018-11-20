<?php


namespace Ayesh\ComposerPreload\Command;

use Ayesh\ComposerPreload\PreloadGenerator;
use Ayesh\ComposerPreload\PreloadList;
use Ayesh\ComposerPreload\PreloadWriter;
use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PreloadCommand extends BaseCommand {

  private $config;

  protected function configure() {
    $this->setName('preload');
    $this->setDescription('Preloads the source files to PHP OPCache to speed up execution.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $composer = $this->getComposer();
    $extra = $composer->getPackage()->getExtra();

    if (empty($extra['preload'])) {
      throw new \RuntimeException('"preload" setting is not set in "extra" section of the composer.json file.');
    }

    if (!\is_array($extra['preload'])) {
      throw new \InvalidArgumentException('"preload" configuration is invalid.');
    }

    $this->setConfig($extra['preload']);
    $list = $this->generatePreload();
    $writer = new PreloadWriter($list);
    $writer->write();

    $io = $this->getIO();
    $io->writeError('Preload file created successfully.');
    $io->writeError(sprintf('Preload script contains %d files.', $writer->getCount()), true, IOInterface::VERBOSE);
  }

  private function setConfig(array $config): void {
    $this->config = $config;
  }

  private function generatePreload(): PreloadList {
    $generator = new PreloadGenerator();

    if (isset($this->config['paths'])) {
      if (!\is_iterable($this->config['paths'])) {
        throw new \InvalidArgumentException(sprintf('"%s" must be an array of paths to preload', 'extra.preload.paths'));
      }

      foreach ($this->config['paths'] as $key => $path) {
        if (!\is_string($path)) {
          throw new \InvalidArgumentException(sprintf('"%s" must be string locating a path in the file system. %s given.',
            'extra.preload.paths.' . $key,
            \gettype($path)
            ));
        }
        $generator->addPath($path);
      }
    }

    return $generator->getList();
  }
}
