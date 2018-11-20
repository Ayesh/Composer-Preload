<?php


namespace Ayesh\ComposerPreload;


use Symfony\Component\Finder\Finder;

class PreloadGenerator {
  private $paths = [];
  private $exclude = [];

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
    $this->paths[] = $path;
  }

  private function findFiles(): void {
    $this->finder = $finder = new Finder();
    $finder->ignoreVCS(true)
      ->ignoreUnreadableDirs()
      ->in($this->paths)
      ->exclude($this->exclude)
      ->name('*.php');
  }

  public function excludePath($path): void {
    $this->exclude[] = $path;
  }
}
