<?hh // strict

namespace HackPack\Scanner\Tests;

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
class FileLoaderTest extends \PHPUnit_Framework_TestCase
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

    <<__Override, Setup>>
    public function setUp() : void
    {
        $this->filesLoaded->clear();
    }

    ///// Tests for inclusion only /////

    <<Test>>
    public function testLoaderRecursesIntoSubDirectories() : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::base},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $this->assertTrue($classes->isEmpty());

        $this->assertEquals(
            $this->filesLoaded,
            $this->fileList(Set{
                IncludeDir::base,
                IncludeDir::ignore,
                IncludeDir::sibling,
            }),
        );
    }

    <<Test>>
    public function testLoaderFindsFilesInSiblingDirectories() : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::ignore, IncludeDir::sibling},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $this->assertTrue($classes->isEmpty());

        $this->assertEquals(
            $this->filesLoaded,
            $this->fileList(Set{
                IncludeDir::ignore,
                IncludeDir::sibling,
            }),
        );
    }

    <<Test>>
    public function testLoaderFindsAllFilesInOneDirectory() : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::sibling},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $this->assertTrue($classes->isEmpty());

        $this->assertEquals(
            $this->fileList(Set{
                IncludeDir::sibling
            }),
            $this->filesLoaded,
        );
    }

    <<Test>>
    public function testLoaderFindsOneFile() : void
    {
        $filename = IncludeDir::base . 'file1.php';
        $loader = new ClassScanner(
            Set{$filename},
            Set{},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $this->assertTrue($classes->isEmpty());
        $this->assertEquals(
            Set{$filename},
            $this->filesLoaded,
        );
    }

    ///// Tests for exclusion /////

    <<Test>>
    public function testLoaderIgnoresDirectory() : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::base},
            Set{IncludeDir::ignore},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $this->assertTrue($classes->isEmpty());
        $this->assertEquals(
            $this->fileList(Set{
                IncludeDir::base,
                IncludeDir::sibling,
            }),
            $this->filesLoaded,
        );
    }

    <<Test>>
    public function testLoaderIgnoresSingleFile() : void
    {
        $loader = new ClassScanner(
            Set{IncludeDir::ignore},
            Set{IncludeDir::ignore . 'file1.php'},
            $this->factory,
        );
        $classes = $loader->mapDefinitionToFile(DefinitionType::CLASS_DEF);
        $this->assertTrue($classes->isEmpty());

        $all = $this->fileList(Set{IncludeDir::ignore});
        $expected = $all->filter($f ==> $f !== IncludeDir::ignore . 'file1.php');

        // Ensure the file being filtered is known
        $this->assertEquals(
            $all->count(),
            $expected->count() + 1,
        );

        // Test the loader
        $this->assertEquals($expected, $this->filesLoaded);
    }
}
