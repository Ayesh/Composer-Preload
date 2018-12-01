<?php


namespace Ayesh\ComposerPreload\Composer\Command;

use Ayesh\ComposerPreload\PreloadGenerator;
use Ayesh\ComposerPreload\PreloadList;
use Ayesh\ComposerPreload\PreloadWriter;
use Ayesh\PHP_Timer\Stopwatch;
use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PreloadCommand extends BaseCommand {

  private $config;

  protected function configure() {
    $this->setName('preload');
    $this->setDescription('Preloads the source files to PHP OPCache to speed up execution.')
    ->setDefinition(array(
      new InputOption('no-status-check', null, InputOption::VALUE_NONE, 'Do not include Opcache status checks in the generated file (useful if you want to combine multiple files).'),
    ))
    ->setHelp(
      <<<HELP
Composer Preload plugin adds this "preload" command, so you can generate a PHP file at 'vendor/preload.php' containing a list of 
PHP files to load into opcache when called. This can significantly speed up your PHP applications if used correctly. 

Use the --no-status-check option to generate the file without additional opcache 
status checks. This can be useful if you want to include the 'vendor/preload.php' 
within another script, so these checks redundent. This will override the 
extra.preload.no-checks directive if used in the composer.json file.

HELP
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $timer = new Stopwatch();
    $composer = $this->getComposer();
    $extra = $composer->getPackage()->getExtra();

    if (empty($extra['preload'])) {
      throw new \RuntimeException('"preload" setting is not set in "extra" section of the composer.json file.');
    }

    if (!\is_array($extra['preload'])) {
      throw new \InvalidArgumentException('"preload" configuration is invalid.');
    }

    $this->setConfig($extra['preload'], $input);
    $list = $this->generatePreload();
    $writer = new PreloadWriter($list);

    if ($this->config['no-status-check']) {
      $writer->setStatusCheck(false);
    }

    $writer->write();

    $io = $this->getIO();
    $io->writeError('<info>Preload file created successfully.</info>');
    $io->writeError(sprintf('<comment>Preload script contains %d files.</comment>', $writer->getCount()), true, IOInterface::VERBOSE);
    $io->writeError(sprintf('<comment>Elapsed time: %.2f sec.</comment>', $timer->read()), true, IOInterface::VERY_VERBOSE);
  }

  private function setConfig(array $config, InputInterface $input): void {
    $this->config = $config;

    if ($input->getOption('no-status-check')) {
      $this->config['no-status-check'] = true;
    }
  }

  private function generatePreload(): PreloadList {
    $generator = new PreloadGenerator();

    $this->validateConfiguration();

    foreach ($this->config['paths'] as $path) {
      $generator->addPath($path);
    }

    foreach ($this->config['exclude'] as $path) {
      $generator->addExcludePath($path);
    }

    $generator->setExcludeRegex($this->config['exclude-regex']);

    foreach ($this->config['extensions'] as $extension) {
      $generator->addIncludeExtension($extension);
    }

    return $generator->getList();
  }

  private function validateConfiguration(): void {
    $force_str_array = ['paths', 'exclude', 'extensions'];
    foreach ($force_str_array as $item) {
      if (!isset($this->config[$item])) {
        $this->config[$item] = [];
      }

      if (!\is_iterable($this->config[$item])) {
        throw new \InvalidArgumentException(sprintf('"%s" must be an array.', 'extra.preload.' . $item));
      }

      foreach ($this->config[$item] as $key => $path) {
        if (!\is_string($path)) {
          throw new \InvalidArgumentException(sprintf('"%s" must be string locating a path in the file system. %s given.',
            "extra.preload.{$path}.{$key}",
            \gettype($path)
          ));
        }
      }
    }

    $force_bool = ['no-status-check' => false];
    foreach ($force_bool as $item => $default_value) {
      if (!isset($this->config[$item])) {
        $this->config[$item] = $default_value;
      }

      if (!\is_bool($this->config[$item])) {
        throw new \InvalidArgumentException(sprintf('"%s" must be boolean value. %s given.',
          'extra.preload.' . $item,
          \gettype($this->config[$item])));
      }
    }

    $force_positive_string = ['exclude-regex' => null];
    foreach ($force_positive_string as $item => $default_value) {
      if (!isset($this->config[$item]) || '' === $this->config[$item]) {
        $this->config[$item] = $default_value;
      }

      if (isset($this->config[$item]) && !\is_string($this->config[$item])) {
        throw new \InvalidArgumentException(sprintf('"%s" must be string value. %s given.',
          'extra.preload.' . $item,
          \gettype($this->config[$item])));
      }
    }
  }
}
