<?php


namespace Ayesh\ComposerPreload;

use Symfony\Component\Finder\Finder;

class PreloadFinder {
  private $include_dirs = [];
  private $exclude_dirs = [];
  private $exclude_subdirs = [];
  private $exclude_regex_static;
  private $files = ['php'];

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
    $this->exclude_regex = null;
    $this->exclude_dirs[] = $dir_name;
  }

  public function addExcludeDirPattern(string $dir_name): void {
    $this->exclude_subdirs[] = $dir_name;
  }

  private function prepareFinder(): void {
    if (empty($this->include_dirs)) {
      throw new \BadMethodCallException('Illegal attempt to get iterator without setting include directory list.');
    }

    foreach ($this->files as $extension) {
      $this->finder->files()->name('*.' . $extension);
    }

    $this->finder->in($this->include_dirs);

    if ($this->exclude_subdirs) {
      $this->finder->exclude($this->exclude_subdirs);
    }

    $exclude_function = $this->getExcludeCallable();
    if ($exclude_function !== null) {
      $this->finder->filter($exclude_function);
    }
  }

  private function getExcludeCallable(): ?callable {
    $regex_dir = $this->getDirectoryExclusionRegex();
    $regex_static = $this->exclude_regex_static;

    if (!$regex_dir && $this->exclude_regex_static === null) {
      return null;
    }

    return function (\SplFileInfo $file) use ($regex_dir, $regex_static): bool {
      $path = str_replace('\\', '/', $file->getPathname());
      $exclude_match = false;
      if ($regex_dir) {
        $exclude_match = preg_match($regex_dir, $path);
      }

      // If excluded due to directory match above , don't run the static regex.
      if (!$exclude_match && $regex_static) {
        $exclude_match = preg_match($regex_static, $path);
      }

      return !$exclude_match;
    };
  }

  protected function getDirectoryExclusionRegex(): ?string {
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
      if (substr($dir, -1) !== '/') {
        $dir .= '/'; // Force all directives to be full direcory paths with "/" suffix.
      }
      $dir = preg_quote($dir, '/');
      $dirs[] = $dir;
    }
    $regex .= implode('|', $dirs);
    $regex .= ')/i';

    $this->exclude_regex = $regex;
    return $regex;
  }

  public function setExcludeRegex(?string $pattern): void {
    if (null !== $pattern) {
      // A parent error handler might catch the errors.
      preg_match($pattern, '', $fake_matched);
      $regex_error = preg_last_error();
      if ($regex_error !== \PREG_NO_ERROR) {
        throw new \InvalidArgumentException(
          sprintf('Preload exclusion regex is invalid: "%s". Error code: %d',
            $pattern, $regex_error),
          $regex_error);
      }
    }

    $this->exclude_regex_static = $pattern;
  }

  public function addIncludeExtension(string $extension): void {
    if (preg_match('/[^A-z0-9]/', $extension) !== 0) {
      throw new \InvalidArgumentException(sprintf('File extension is not valid: "%s"', $extension));
    }

    $this->files[] = $extension;
    $this->files = array_unique($this->files);
  }
}
