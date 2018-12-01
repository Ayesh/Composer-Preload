<?php

namespace Ayesh\ComposerPreload\Tests;

use Ayesh\ComposerPreload\PreloadFinder;
use PHPUnit\Framework\TestCase;

class PreloadFinderInvalidValuesTest extends TestCase {
  public function testGetIteratorInvalidState(): void {
    $finder = new PreloadFinder();
    $this->expectException(\BadMethodCallException::class);
    $finder->getIterator();
  }
}
