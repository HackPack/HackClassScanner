HackClassScanner
================
[![Build Status](https://travis-ci.org/kilahm/HackClassScanner.svg)](https://travis-ci.org/kilahm/HackClassScanner) [![HHVM Status](http://hhvm.h4cc.de/badge/kilahm/hack-class-scanner.svg)](http://hhvm.h4cc.de/package/kilahm/hack-class-scanner)

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

$scanner = new ClassScanner(
  Set{‘directory/to/scan’, ‘other/directory’},
  Set{‘other/directory/to/ignore’}
);
$classMap = $scanner->mapClassToFile();
$classAndInterfaceMap = $scanner->mapClassOrInterfaceToFile();
```

The `$classMap` variable will then hold a `Map<string,string>` object that maps class names (with full namespace) to the files in which the class is defined.
The `$classAndInterfaceMap` will be the same as `$classMap` except it will include interfaces as well as classes.

## Assumptions

This class assumes you are following the practice of one class per file.  The scanner will stop searching once it has found the first class name in a file.

## Helper Script

An executable hack script is included in this library if you simply want to view a list of classes defined in a particular directory.

```bash
$ vendor/bin/scan path/to/scan [other/path ...] [--exclude=”path/to/exclude [other/path/to/exclude]”]
```

Thanks
======

The file parsing algorithm was heavily influenced by the one used in [HackPack/HackUnit](https://github.com/HackPack/HackUnit).
