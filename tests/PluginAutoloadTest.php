<?php

namespace Ayesh\ComposerPreload\Tests;

use Ayesh\ComposerPreload\Composer\Command\PreloadCommand;
use Ayesh\ComposerPreload\Composer\Command\PreloadCommandProvider;
use Ayesh\ComposerPreload\Composer\Plugin;
use PHPUnit\Framework\TestCase;

class PluginAutoloadTest extends TestCase {
  public function testAutoload(): void {
    $this->assertTrue(class_exists(Plugin::class));
    $this->assertTrue(class_exists(PreloadCommandProvider::class));
    $this->assertTrue(class_exists(PreloadCommand::class));
  }
}
