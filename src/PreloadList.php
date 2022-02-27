<?php

namespace Ayesh\ComposerPreload;

use BadMethodCallException;
use IteratorAggregate;
use Traversable;

final class PreloadList implements IteratorAggregate {

    /**
     * @var IteratorAggregate
     */
    private $list;

    public function setList(iterable $list): void {
        $this->list = $list;
    }

    public function getIterator(): Traversable {
        if (!$this->list) {
            throw new BadMethodCallException('Attempting to fetch the iterator without setting one first.');
        }
        return $this->list;
    }
}
