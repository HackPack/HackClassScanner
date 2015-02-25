HackClassScanner
================
[![Build Status](https://travis-ci.org/HackPack/HackClassScanner.svg)](https://travis-ci.org/HackPack/HackClassScanner)
[![HHVM Status](http://hhvm.h4cc.de/badge/hackpack/hack-class-scanner.svg)](http://hhvm.h4cc.de/package/hackpack/hack-class-scanner)

A class that recursively scans a directory for hack classes.

Installation
===========

Install [Composer](https://getcomposer.org/download/) then use the following in your project directory:

```bash
composer require hackpack/hack-class-scanner
```

Use
===

To use the class, simply instantiate it with a set of files/base directories to scan and a set of files/directories to ignore.

```php
use HackPack\Scanner\ClassScanner;

$scanner = new ClassScanner(
  Set{‘directory/to/scan’, ‘other/directory’, ‘file/to/scan.txt’},
  Set{‘other/directory/to/ignore’, ‘other/directory/file_to_ignore.txt’}
);
$classMap = $scanner->mapClassToFile();
$classAndInterfaceMap = $scanner->mapClassOrInterfaceToFile();
```

The `$classMap` variable will then hold a `Map<string,string>` object that maps class names (with full namespace) to the files in which the class is defined.
The `$classAndInterfaceMap` will be the same as `$classMap` except it will include interfaces as well as classes.

## Filters

You can filter the result files based on the name of the class or the name of the file. A filter must be a closure with a signature of `function(string) : bool`.
The input for a class filter is the name of the class including the namespace.  The input for a file filter is the name of the file including the full path (via `realpath`).
If all registered filter functions return `true` for a particular file or class name, the file will be scanned for a class and the class name will appear in the output, respectively.

```php
use HackPack\Scanner\ClassScanner;

$includes = Set{...};
$excludes = Set{...};
$scanner = new ClassScanner($includes, $excludes);
$classFilter = $className ==> preg_match(‘/pattern/’, $className);
$fileFilter = $fileName ==> preg_match(‘/pattern/’, $fileName);
$scanner->addClassNameFilter($classFilter);
$scanner->addFileNameFilter($fileFilter);
$classMap = $scanner->mapClassToFile();
```

## Assumptions

This class assumes you are following the practice of one class per file.  The scanner will stop searching once it has found the first class name in a file.

Thanks
======

The file parsing algorithm was heavily influenced by the one used in [HackPack/HackUnit](https://github.com/HackPack/HackUnit).
