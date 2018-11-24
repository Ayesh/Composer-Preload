<?php

namespace Ayesh\ComposerPreload\Tests;

use Ayesh\ComposerPreload\PreloadList;
use PHPUnit\Framework\TestCase;

class PreloadListTest extends TestCase {

  public function testSetList(): void {
    $iterator = ['test' => 'test'];
    $list = new PreloadList();
    $list->setList($iterator);

    $this->expectException(\TypeError::class);
    $list->setList(new \stdClass());
  }

  public function testGetIterator(): void {
    $iterator = ['test' => base64_encode(random_bytes(12))];
    $list = new PreloadList();
    $list->setList($iterator);
    $this->assertSame($iterator, $list->getIterator());

    $list = new PreloadList();
    $this->expectException(\BadMethodCallException::class);
    $list->getIterator();
  }
}
