<?php


namespace Ayesh\ComposerPreload;


use Ayesh\ComposerPreload\Command\PreloadCommandProvider;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capability\CommandProvider;

class Plugin implements  PluginInterface, Capable {
  /**
   * Apply plugin modifications to Composer
   *
   * @param Composer $composer
   * @param IOInterface $io
   */
  public function activate(Composer $composer, IOInterface $io): void {

  }

  public function getCapabilities(): array {
    return array(
      CommandProvider::class => PreloadCommandProvider::class,
    );
  }
}
