<?php


namespace Ayesh\ComposerPreload;


use Traversable;

final class PreloadList implements \IteratorAggregate {

  /**
   * @var \IteratorAggregate
   */
  private $list;

  public function setList(iterable $list): void {
    $this->list = $list;
  }

  public function getIterator(): Traversable {
    return $this->list;
  }
}
