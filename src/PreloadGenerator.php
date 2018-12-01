<?php


namespace Ayesh\ComposerPreload;

class PreloadGenerator {
  /**
   * @var PreloadFinder
   */
  private $finder;

  public function __construct() {
    $this->finder = new PreloadFinder();
  }

  public function getList(): PreloadList {
    $list = new PreloadList();
    $list->setList($this->finder->getIterator());
    return $list;
  }

  public function addPath(string $path): void {
    $this->finder->addIncludePath($path);
  }

  public function addExcludePath(string $path): void {
    $this->finder->addExcludePath($path);
  }

  public function setExcludeRegex(?string $pattern): void {
    $this->finder->setExcludeRegex($pattern);
  }

  public function addIncludeExtension(string $extension): void {
    $this->finder->addIncludeExtension($extension);
  }
}
