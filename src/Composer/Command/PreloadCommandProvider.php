<?php


namespace Ayesh\ComposerPreload\Composer\Command;

use Composer\Command\BaseCommand;
use Composer\Plugin\Capability\CommandProvider;

class PreloadCommandProvider implements CommandProvider {

    /**
     * Retrieves an array of commands
     *
     * @return BaseCommand[]
     */
    public function getCommands(): array {
        return [
            new PreloadCommand(),
        ];
    }
}
