<?php


namespace Ayesh\ComposerPreload\Composer\Command;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class PreloadCommandProvider implements CommandProviderCapability {

  /**
   * Retrieves an array of commands
   *
   * @return \Composer\Command\BaseCommand[]
   */
  public function getCommands(): array {
    return [
      new PreloadCommand(),
    ];
  }
}
