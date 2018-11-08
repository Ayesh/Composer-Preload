<?php

namespace Ayesh\ComposerPreload\Tests;

use Ayesh\ComposerPreload\Command\PreloadCommand;
use Ayesh\ComposerPreload\Command\PreloadCommandProvider;
use Ayesh\ComposerPreload\Plugin;
use PHPUnit\Framework\TestCase;

class PluginAutoloadTest extends TestCase {
  public function testAutoload() {
    $this->assertTrue(class_exists(Plugin::class));
    $this->assertTrue(class_exists(PreloadCommandProvider::class));
    $this->assertTrue(class_exists(PreloadCommand::class));
  }
}
