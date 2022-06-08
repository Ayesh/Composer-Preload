<?php


namespace Ayesh\ComposerPreload\Composer\Command;

use Ayesh\ComposerPreload\PreloadGenerator;
use Ayesh\ComposerPreload\PreloadList;
use Ayesh\ComposerPreload\PreloadWriter;
use Ayesh\PHP_Timer\Formatter;
use Ayesh\PHP_Timer\Stopwatch;
use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function gettype;
use function is_array;
use function is_bool;
use function is_iterable;
use function is_string;

class PreloadCommand extends BaseCommand {

    private $config;

    protected function configure() {
        $this->setName('preload');
        $this->setDescription('Preloads the source files to PHP OPCache to speed up execution.')
            ->setDefinition(
                array(
                    new InputOption(
                        'no-status-check',
                        null,
                        InputOption::VALUE_NONE,
                        'Do not include Opcache status checks in the generated file (useful if you want to combine multiple files).'
                    ),
                )
            )
            ->setHelp(
                <<<HELP

Composer Preload plugin adds this "preload" command, so you can generate a PHP file at 'vendor/preload.php' containing a list of PHP files to load into opcache when called. This can significantly speed up your PHP applications if used correctly. 

Use the --no-status-check option to generate the file without additional opcache status checks. This can be useful if you want to include the 'vendor/preload.php' within another script, so these checks redundent. This will override the extra.preload.no-checks directive if used in the composer.json file.


Example configuration for `composer.json`:

-----------------
"extra": {
        "preload": {
            "paths": [
                "vendor/example/example-1/src",
                "vendor/ayesh/php-timer/src",
                "drupal-core"
            ],
            "exclude": [
                "web/core/tests",
                "vendor/example/example-2/src",
                "web/core/modules/simpletest",
                "web/core/modules/editor/src/Tests"
            ],
            "extensions": ["php", "module", "inc", "install"],
            "exclude-regex": "/[A-Za-z0-9_]test\\.php$/i",
            "no-status-check": false,
            "files": [
                "somefile.php"
            ]
        }
    }
-----------------

 - paths: An array of paths to scan for files
 - extensions: An array of extensions (without the dot) to filter file extensions
 - files: An array of individual files to include.
 - exclude: An array of paths to exclude from the preload file, even if they match "paths" directive.
 - no-status-check: A boolean indicating whether the generated preload file should skip extra checks or not
 - exclude-regex: A regular expression to run on the full file path, and if matched, to be excluded from preload list.
 
For more: https://github.com/Ayesh/Composer-Preload
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $timer = new Stopwatch();
        $composer = $this->getComposer();
        $extra = $composer->getPackage()->getExtra();

        if (empty($extra['preload'])) {
            throw new RuntimeException('"preload" setting is not set in "extra" section of the composer.json file.');
        }

        if (!is_array($extra['preload'])) {
            throw new InvalidArgumentException('"preload" configuration is invalid.');
        }

        $this->setConfig($extra['preload'], $input);
        $list = $this->generatePreload();
        $writer = new PreloadWriter($list);

        if ($this->config['no-status-check']) {
            $writer->setStatusCheck(false);
        }

        // Todo: Add support for configurable preload file destiations.

        $writer->write();

        $io = $this->getIO();
        $io->writeError('<info>Preload file created successfully.</info>');
        $io->writeError(
            sprintf('<comment>Preload script (<info>%s</info>) contains <info>%d</info> files.</comment>', $writer->getPath(), $writer->getCount()),
            true
        );

        $ms = (int) \round($timer->read() * 1000);

        $io->writeError(
            sprintf('<comment>Elapsed time: <info>%s</info>.</comment>', Formatter::formatTime($ms)),
            true
        );
        
        return 0;
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

        foreach ($this->config['files'] as $file) {
            $generator->addFile($file);
        }

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
        $force_str_array = ['paths', 'exclude', 'extensions', 'files'];
        foreach ($force_str_array as $item) {
            if (!isset($this->config[$item])) {
                $this->config[$item] = [];
            }

            if (!is_iterable($this->config[$item])) {
                throw new InvalidArgumentException(sprintf('"%s" must be an array.', 'extra.preload.' . $item));
            }

            foreach ($this->config[$item] as $key => $path) {
                if (!is_string($path)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            '"%s" must be string locating a path in the file system. %s given.',
                            "extra.preload.{$path}.{$key}",
                            gettype($path)
                        )
                    );
                }
            }
        }

        $force_bool = ['no-status-check' => false];
        foreach ($force_bool as $item => $default_value) {
            if (!isset($this->config[$item])) {
                $this->config[$item] = $default_value;
            }

            if (!is_bool($this->config[$item])) {
                throw new InvalidArgumentException(
                    sprintf(
                        '"%s" must be boolean value. %s given.',
                        'extra.preload.' . $item,
                        gettype($this->config[$item])
                    )
                );
            }
        }

        $force_positive_string = ['exclude-regex' => null];
        foreach ($force_positive_string as $item => $default_value) {
            if (!isset($this->config[$item]) || '' === $this->config[$item]) {
                $this->config[$item] = $default_value;
            }

            if (isset($this->config[$item]) && !is_string($this->config[$item])) {
                throw new InvalidArgumentException(
                    sprintf(
                        '"%s" must be string value. %s given.',
                        'extra.preload.' . $item,
                        gettype($this->config[$item])
                    )
                );
            }
        }
    }
}
