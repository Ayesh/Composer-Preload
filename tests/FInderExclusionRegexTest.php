<?php

namespace Ayesh\ComposerPreload\Tests;

use Ayesh\ComposerPreload\PreloadFinder;
use PHPUnit\Framework\TestCase;

class FInderExclusionRegexTest extends TestCase {
  public function getMockupRegexBuilder(): PreloadFinder {
    return  new class() extends PreloadFinder {
      public function getExcludeRegex(): ?string {
        return parent::getExcludeRegex();
      }
    };
  }

  public function testRegex(): void {
    $class = $this->getMockupRegexBuilder();
    $finder = new $class();
    /**
     * @var $finder PreloadFinder
     */

    $this->assertNull($finder->getExcludeRegex());

    $finder->addExcludePath('test');
    $this->assertSame('/^(test\/)/i', $finder->getExcludeRegex());

    $finder->addExcludePath('test2');
    $this->assertSame('/^(test\/|test2\/)/i', $finder->getExcludeRegex());

    $finder->addExcludePath('src\\');
    $this->assertSame('/^(test\/|test2\/|src\/)/i', $finder->getExcludeRegex());

    $finder->addExcludePath('src/');
    $this->assertSame('/^(test\/|test2\/|src\/|src\/)/i', $finder->getExcludeRegex());
  }
}
