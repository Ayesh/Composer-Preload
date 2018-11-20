# Composer Preload

Preload your sweet sweet code to opcache with a composer command, making your code faster to run

Composer Preload is a composer plugin aiming to provide and complement PHP opcache warming.
This plugin introduces a new `composer preload` command that can generate a `vendor/preload.php` file (following `vendor/autoload.php` pattern) that contains calls to warm up the opcache cache.

Please note that this plugin is currently in a very rudimentary state, and it is highly recommend to not use this in any production system.

# How it works

At the moment, this plugin scans for `.php` files in the given paths recursively, and create a file that calls `opcache_compile_file` function.
When you want to warm up the cache, you can either call `php vendor/preload.php` in command line, or when PHP 7.4 hits the shelves, configure PHP to automatically load this file.

# Installation

Just the way you'd install a normal composer package, you can install this plugin aswell:
```
composer require ayesh/composer-preload
```
If you'd rather install this globally:
```
composer g require ayesh/composer-preload
```

# Configuration

Before you can run the `composer preload` command, you **must** define the directories to preload.

Modify your `composer.json` file, and create a section called `extra` if it's not there already.
```
{
  "extra": {
	"preload": {
	    "paths": [
		    "src"
	    ]
    }
  }
}
```
The `extra.preload` directive contains all the configuration options for this plugin. The `paths` directive must be an array of directories relative to the `composer.json` file. These directories will be scanned recursively for `.php` files, converted to absolute paths, and appended to the `vendor/preload.php` file.


# Roadmap

 - Extend `extras.preload` section to configure the packages that should be preloaded instead of setting the individual paths.
 - Feature to set an exclude pattern.
 - Progress bar to show the file generation progress
 - Flag to generate the file _and_ run it, so the cache is immediately warmed up.
 - Fancier progress bar.
 - Full test coverage.
 - Even more fancier progress bar with opcache memory usage display, etc.
 - Get many Github stars
