HackClassScanner
================

A class that recursively scans a directory for hack classes.

Installation
===========
Add the following line to your `composer.json` file in the `require` section.

```
“kilahm/hack-class-scanner”: “dev-master”
```

Then run `composer update`.

Use
===

To use the class, simply instantiate it with a set of base directories to scan and a set of directories to ignore.

```php
use kilahm\Scanner\ClassScanner;

$scanner = new ClassScanner(Set{‘directory/to/scan’, ‘other/directory’}, Set{‘directory/to/scan/ignore’});
$classMap = $scanner->mapFileToClass();
```

The `$classMap` variable will then hold a `Map<string,string>` object that maps class names (with full namespace) to the files in which the class is defined.

Thanks
=====

The file parsing algorithm was heavily influenced by the one used in [HackPack/HackUnit](https://github.com/HackPack/HackUnit).
