<?php


namespace Ayesh\ComposerPreload;

use Symfony\Component\Finder\Finder;

class PreloadFinder {
  private $include_dirs = [];
  private $exclude_dirs = [];
  private $exclude_subdirs = [];
  private $files = ['*.php'];

  /**
   * @var \Symfony\Component\Finder\Finder
   */
  private $finder;

  private $exclude_regex;

  public function __construct() {
    $this->finder = new Finder();
  }

  public function getIterator(): iterable {
    $this->prepareFinder();
    return $this->finder->getIterator();
  }

  public function addIncludePath(string $dir_name): void {
    $this->include_dirs[] = $dir_name;
  }

  public function addExcludePath(string $dir_name): void {
    $this->exclude_dirs[] = $dir_name;
  }

  public function addExcludeDirPattern(string $dir_name): void {
    $this->exclude_subdirs[] = $dir_name;
  }

  private function prepareFinder(): void {
    if (empty($this->include_dirs)) {
      throw new \InvalidArgumentException('Include directive cannot be empty.');
    }

    $this->finder->files()->name($this->files);
    $this->finder->in($this->include_dirs);

    if ($this->exclude_subdirs) {
      $this->finder->exclude($this->exclude_subdirs);
    }

    $regex = $this->getExcludeRegex();

    if (!$regex) {
      return;
    }

    $this->finder->filter(function (\SplFileInfo $file) use ($regex) {
      /**
       * @var \SplFileInfo $file
       */
      $path = str_replace('\\', '/', $file->getPathname());
      return !preg_match($regex, $path);
    });
  }

  private function getExcludeRegex(): ?string {
    if ($this->exclude_regex !== NULL) {
      return $this->exclude_regex;
    }

    if (empty($this->exclude_dirs)) {
      return null;
    }
    $regex = '/^(';
    $dirs = [];
    foreach ($this->exclude_dirs as $dir) {
      $dir = str_replace('\\', '/', $dir);
      $dir = preg_quote($dir, '/');
      $dirs[] = $dir;
    }
    $regex .= implode('|', $dirs);
    $regex .= ')/i';

    $this->exclude_regex = $regex;
    return $regex;
  }
}
