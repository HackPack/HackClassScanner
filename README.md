HackClassScanner
================
[![Build Status](https://travis-ci.org/HackPack/HackClassScanner.svg)](https://travis-ci.org/HackPack/HackClassScanner)
[![HHVM Status](http://hhvm.h4cc.de/badge/hackpack/hack-class-scanner.svg)](http://hhvm.h4cc.de/package/hackpack/hack-class-scanner)

A class that recursively scans a directory for [Hack](http://hacklang.org/) definitions of:

* Classes
* Interfaces
* Traits
* Enums
* Type definitions
* NewType definitions
* Functions
* Constants

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
use HackPack\Scanner\DefinitionType;

$scanner = new ClassScanner(
  Set{‘directory/to/scan’, ‘other/directory’, ‘file/to/scan.txt’},
  Set{‘other/directory/to/ignore’, ‘other/directory/file_to_ignore.txt’}
);

$classes = $scanner->get(DefinitionType::CLASS_DEF);
$interfaces = $scanner->get(DefinitionType::INTERFACE_DEF);
$traits = $scanner->get(DefinitionType::TRAIT_DEF);
$enums = $scanner->get(DefinitionType::ENUM_DEF);
$types = $scanner->get(DefinitionType::TYPE_DEF);
$newtypes = $scanner->get(DefinitionType::NEWTYPE_DEF);
$functions = $scanner->get(DefinitionType::FUNCTION_DEF);
$constants = $scanner->get(DefinitionType::CONST_DEF);
```

The `get` method takes one parameter specifying the type of the definition desired.  `get` will return a `Map<string,string>` where the keys are the names of the definitions
and the values are the full path to the file in which the definition resides.

Please note that ALL files will be scanned by default (not just .php and/or .hh files).  If you wish to only scan files with a particular extension, see the file name filter section below.

## Filters

You can filter the results based on the name of the definition and/or the name of the file. Each filter must be a closure with a signature of `function(string) : bool`.

### File Name Filters

To register a definition name filter, call `ClassScanner->addFileNameFilter()` with the filter callback (see example below).

The input for a file name filter is the full path to the file (via `realpath`).  If all registered filter functions return `true` for a particular file name, the file will be scanned.
If any of the filters return `false`, the file will not be read (via `file_get_contents`).

### Definition Name Filters

To register a definition name filter, call `ClassScanner->addDefinitionNameFilter()` with the definition type and the filter callback (see example below).

The input for a definition name filter is the name of the definition including the namespace. If any registered filter function returns `false` for a particular name, that name
is guaranteed to not appear in the results. Note that the file in which a filtered definition appears may still be in the list if other non-filtered definitions are also in said file.
If you would like to guarantee a file to be skipped, define a file name filter.

### Example Filters

In this example, a simple regular expression is used to filter both file names and class names.
```php
use HackPack\Scanner\ClassScanner;
use HackPack\Scanner\DefinitionType;

$includes = Set{...};
$excludes = Set{...};
$scanner = new ClassScanner($includes, $excludes);

// Define the filter callbacks
$classFilter = $className ==> preg_match(‘/pattern/’, $className);
$fileFilter = $fileName ==> preg_match(‘/pattern/’, $fileName);

// Attach the filters
$scanner->addDefinitionNameFilter(DefinitionType::CLASS_DEF, $classFilter);
$scanner->addFileNameFilter($fileFilter);

// Retreive the class definitions
$classMap = $scanner->mapDefinitionToFile(DefinitionType::CLASS_DEF);
```

In this example, we are specifically looking for XHP classes, using the assumption that all XHP classes are defined in `.xhp` files.
```php
use HackPack\Scanner\ClassScanner;
use HackPack\Scanner\DefinitionType;

$includes = Set{...};
$excludes = Set{...};
$scanner = new ClassScanner($includes, $excludes);

// Define the filters
$classFilter = $className ==> substr($className, 0, 4) === 'xhp_';
$fileFilter = $fileName ==> substr($fileName, -4) === '.xhp';

// Attach the filters
$scanner->addDefinitionNameFilter(DefinitionType::CLASS_DEF, $classFilter);
$scanner->addFileNameFilter($fileFilter);

// Retreive the class definitions
$xhpClasses = $scanner->mapClassToFile();
```

Thanks
======

The file parsing algorithm and the majority of the tests were authored by [Fred Emmott](https://github.com/fredemmott) in [fredemmott/definitions-finder](https://github.com/fredemmott/definitions-finder)

