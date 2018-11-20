<?php


namespace Ayesh\ComposerPreload;


use Symfony\Component\Finder\Finder;

class PreloadGenerator {
  private $paths = [];

  /**
   * @var Finder
   */
  private $finder;

  public function getList(): PreloadList {
    $this->findFiles();
    $list = new PreloadList();
    $list->setList($this->finder->getIterator());
    return $list;
  }

  public function addPath(string $path): void {
    $this->paths = $path;
  }

  private function findFiles(): void {
    $this->finder = $finder = new Finder();
    $finder->ignoreVCS(true)
      ->ignoreUnreadableDirs()
      ->in($this->paths)
      ->name('*.php');
  }
}
