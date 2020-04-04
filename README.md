# Composer Preload

Preload your sweet sweet code to opcache with a composer command, making your code run faster.

Composer Preload is a composer plugin aiming to provide and complement PHP opcache warming.
This plugin introduces a new `composer preload` command that can generate a `vendor/preload.php` file (following 
`vendor/autoload.php` pattern) that contains calls to warm up the opcache cache.

Please note that this plugin is currently in a very rudimentary state, and it is highly recommend to not use this in 
any production system. Any contributions are warmly welcome!

# How it works

At the moment, this plugin scans for `.php` files in the given paths recursively, and create a file that calls 
`opcache_compile_file` function.

When you want to warm up the cache, you can either call `php vendor/preload.php` in command line, or when PHP 7.4 hits 
the shelves, configure PHP to automatically load this file.

# Installation

Just the way you'd install a normal composer package, you can install this plugin aswell:
```
composer require ayesh/composer-preload
```
If you would rather install this globally:
```
composer g require ayesh/composer-preload
```

# Configuration

1: Modify your `composer.json` file, and create a section called `extra` if it's not there already. Following is an 
example:

```
{
    "extra": {
        "preload": {
            "paths": [
                "web"
            ],
            "exclude": [
                "web/core/tests",
                "web/core/lib/Drupal/Component/Assertion",
                "web/core/modules/simpletest",
                "web/core/modules/editor/src/Tests"
            ],
            "extensions": ["php", "module", "inc", "install"],
            "exclude-regex": "/[A-Za-z0-9_]test\\.php$/i",
            "no-status-check": false,
            "files": [
                "somefile.php"
            ]
        }
    }
}
```
The `extra.preload` directive contains all the configuration options for this plugin. The `paths` directive must be an 
array of directories relative to the `composer.json` file. These directories will be scanned recursively for `.php` 
files, converted to absolute paths, and appended to the `vendor/preload.php` file.

2: Run the `composer preload` command.

3: Execute the generated `vendor/preload.php` file. You can either run `php vendor/preload.php` or use your web server 
to execute it. See the Preloading section below for more information.


## Configuration options

 - `extra.preload.paths` : _Required_

An array of directory paths to look for `.php` files in. This setting is required as of now. The directories must exist
 at the time `composer preload` command is run.

 - `extra.preload.exclude` : _Optional_

An array of directory paths to exclude from the `preload.php`. This list must be relative to the composer.json file, 
similar to the `paths` directive. The ideal use case limiting the scope of the `paths` directive.

 - `extra.preload.extensions` : _Optional_, Default: `["php"]`

An array of file extensions to search for. If not entered, it will search for all `.php` files.
Do not enter the proceeding period (`.`) character. The example above is suitable for Drupal. For Symfony/Laravel projects,
you can leave the default option `["php"]` or just not use this option so it defaults to just `.php`.

 - `extra.preload.exclude-regex` : _Optional_

Set a PCRE compatible full regular expression (with delimiters and modifiers included) that will be matched against
the full path, and if matched, will be excluded from the preload list. This can help you exclude tests from the preload
list.

For example, to exclude all PHPUnit-akin tests, you can use the regular expression `/[A-Za-z0-9_]test\\.php$/i`.
This will make sure the file name ends with "test.php", but also has an alphanumeric or underscore prefix. This is 
a common pattern of PHPUnit tests. The `/i` modifier makes the match case insensitive. 

For directory separators, always use Unix-style forward slashes (`/`) even if you are on a Windows system that uses 
backwards slashes (`\`). Don't forget to properly escape the regex pattern to work within JSON syntax; e.g escape
slashes (`\` and `/`) with a backwards slash (`\` -> `\\` and `/` -> `\/`). This will make the regular expression
hard to read, but ¯\\_(ツ)_/¯.

 - `extra.preload.no-status-check`: _Optional_, Default: _`false`_

If this setting is set to `true` (you can also pass command line option `--no-status-check`), make the generated 
`preload.php` file not contain additional checks to make sure the opcache is enabled. This setting is disabled by 
default, and the generated `preload.php` file will contain a small snippet on the top that makes it quit if opcache is 
not enabled.

 - `extra.preload.files` : _Optional_

An array of single files to be included. This setting is optional. The files must exist
 at the time `composer preload` command is run.

# Preloading

To do the actual preloading, execute `vendor/preload.php`. 

If you have enabled opcache for CLI applications, you can directly call `php vendor/preload.php` to execute the 
generated PHP file and warm up the cache right away. 

Future versions of this plugin will have a feature to generate the file _and_ immediately run it.

In a webserver context, or when you cannot run the PHP file with the CLI `php` binary. this probably means you'll 
want to link `vendor/preload.php` into your docroot somwhere and curl it. For example, 
`ln -s vendor/preload.php path/to/docroot/preload.php` and then `curl localhost/preload.php` on webserver startup.


# FAQ

### What does this plugin even do?

This plugin can create a new file at `vendor/preload.php` that follows the pattern of Composer's autoloader at 
`vendor/autoload.php`. This new `preload.php` file contains several function calls that compiles PHP files and cache
them into PHP's opcache. PHP Opcache is a shared memory (with optional file storage option) feature in PHP that can
hold compiled PHP files, so the same file doesn't need to be compiled again and again when its called. This is a
persistent memory until PHP is restarted or the cache is eventually cleared. 

Caching files in opcache has siginificant performance benefits for the cost of memory.

### So all the files are loaded all the time?

All the files are loaded to the _Opcache_. This is **not** same as you `include()` or `require()` a class, which makes
PHP actually execute the code. When you cache code to Opcache, those classes are not executed - just their compiled code
is cached to the memory. 

For example, if you declare a variable, this plugin's preload functionality will not make the variables available inside
your PHP code. You still have to include the file to make them available. 

### I have the `vendor/preload.php` file. What now?

After generating the file, you might need to actaully run it effectively load the files to Opcache. Ideally, you should
do this every time you restart your web server or PHP server, depending on how you serve PHP within your web server. 

PHP 7.4 has a `php.ini` option `opcache.preload` that you can specify this generated file, or a separate file that calls
all `vendor/preload.php` files you have across your server to actively warm up the cache. 

### I have multiple Composer projects running on same server. 

You can generate the preload file for each project, and include all of them in a separate PHP file you create by
yourself. Then, call all of the generated `vendor/preload.php` files. 

By default, the preload file will contain a small snippet at the top that will quit the script immediately if Opcache
is not available. If you plan to include this `vendor/preload.php` file from another script, you can use the special
command line option `composer preload --no-status-check` that will make the `vendor/preload.php` file not contain these
checks, so you can incude multiple `vendor/preload.php` files across all your projects without running the same snippet
over and over. It is recommended that you make sure Opcache is enabled before doing so. Feel free to copypasta the
snippet from one of your generated preload files. 

### Can I generate the preload file in one server and use it in another server?

Yes. Similar to the `vendor/autoload.php` file, `vendor/preload.php` file this plugin generates also uses relative paths. 
From version `v0.1.0` and forward, you can generate the preload file at one server and reuse it in other servers (directory hierarchies).

You will still need to run the `vendor/preload.php` file in all servers that you want to preload opcache. 

# Roadmap

 - ☐ Extend `extras.preload` section to configure the packages that  should be preloaded instead of setting the individual paths.
 - ✓ Feature to set an exclude pattern (v0.0.3)
 - ☐ Progress bar to show the file generation progress
 - ☐ Flag to generate the file _and_ run it, so the cache is immediately warmed up.
 - ☐ Fancier progress bar.
 - ⭕ Full test coverage.
 - ☐ Even more fancier progress bar with opcache memory usage display, etc.
 - ☐ Get many Github stars
