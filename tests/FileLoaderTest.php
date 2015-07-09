<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\ClassScanner;
use HackPack\Scanner\DefinitionFinder;
use HackPack\Scanner\DefinitionType;

enum IncludeDir : string as string
{
    base = __DIR__ . '/data/files/';
    sibling = __DIR__ . '/data/files/sibling/';
    ignore = __DIR__ . '/data/files/ignore/';
}

<<TestSuite>>
class FileLoaderTest
{
    private Set<string> $filesLoaded = Set{};
    private MockDefinitionFinder $finder;
    private (function(string):DefinitionFinder) $factory;

    // File extension agnostic.  Code can live in any file.
    private \ConstVector<string> $filenames = Vector{
        'file1.php',
        'file2.txt',
        'file3'
    };

    public function __construct()
    {
        $this->finder = new MockDefinitionFinder();
        $this->factory = (string $filename) ==> {
            $this->filesLoaded->add($filename);
            return $this->finder;
        };
    }

    private function fileList(Set<IncludeDir> $dirs) : Set<string>
    {
        $out = Set{};
        foreach($dirs as $dir) {
            $out->addAll($this->filenames->map($n ==> $dir . $n));
        }
        return $out;
    }

    <<Setup>>
    public function clearFileList() : void
    {
        $this->filesLoaded->clear();
    }

    ///// Tests for inclusion only /////

    <<Test>>
    public function loaderRecursesIntoSubDirectories(Assert $assert) : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::base},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $assert->bool($classes->isEmpty())->is(true);

        $assert->mixed($this->filesLoaded)
            ->looselyEquals($this->fileList(Set{IncludeDir::base, IncludeDir::ignore, IncludeDir::sibling}));
    }

    <<Test>>
    public function loaderFindsFilesInSiblingDirectories(Assert $assert) : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::ignore, IncludeDir::sibling},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $assert->bool($classes->isEmpty())->is(true);

        $assert->mixed($this->filesLoaded)
            ->looselyEquals($this->fileList(Set{
                IncludeDir::ignore,
                IncludeDir::sibling,
            }));
    }

    <<Test>>
    public function loaderFindsAllFilesInOneDirectory(Assert $assert) : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::sibling},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $assert->bool($classes->isEmpty())->is(true);

        $assert->mixed($this->filesLoaded)
            ->looselyEquals($this->fileList(Set{
                IncludeDir::sibling
            }));
    }

    <<Test>>
    public function loaderFindsOneFile(Assert $assert) : void
    {
        $filename = IncludeDir::base . 'file1.php';
        $loader = new ClassScanner(
            Set{$filename},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $assert->bool($classes->isEmpty())->is(true);

        $assert->mixed($this->filesLoaded)
            ->looselyEquals(Set{$filename});
    }

    ///// Tests for exclusion /////

    <<Test>>
    public function loaderIgnoresDirectory(Assert $assert) : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::base},
            Set{IncludeDir::ignore},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $assert->bool($classes->isEmpty())->is(true);

        $assert->mixed($this->filesLoaded)
            ->looselyEquals($this->fileList(Set{
                IncludeDir::base,
                IncludeDir::sibling,
            }));
    }

    <<Test>>
    public function loaderIgnoresSingleFile(Assert $assert) : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::ignore},
            Set{IncludeDir::ignore . 'file1.php'},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $assert->bool($classes->isEmpty())->is(true);

        $all = $this->fileList(Set{IncludeDir::ignore});
        $expected = $all->filter($f ==> $f !== IncludeDir::ignore . 'file1.php');

        // Ensure the file being filtered is known
        $assert->int($expected->count() + 1)->eq($all->count());

        // Test the loader
        $assert->mixed($this->filesLoaded)
            ->looselyEquals($expected);
    }
}
