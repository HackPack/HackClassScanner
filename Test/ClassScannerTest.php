<?hh // strict

namespace kilahm\Test;

use kilahm\Scanner\ClassScanner;
use HackPack\HackUnit\Core\TestCase;

class ClassScannerTest extends TestCase
{
    private Map<string,string> $allClasses = Map{
        '\ClassA' => __DIR__ . '/Fixtures/ClassA.php',
        '\kilahm\Random\NS\ClassB' => __DIR__ . '/Fixtures/SubSpace/ClassB.php',
        '\kilahm\RandomOther\NS\ClassC' => __DIR__ . '/Fixtures/SubSpace/ClassC.php',
    };

    private Map<string,string> $topClasses = Map{
        '\ClassA' => __DIR__ . '/Fixtures/ClassA.php',
    };

    private Map<string,string> $allClassesAndInterfaces = Map{
        '\ClassA' => __DIR__ . '/Fixtures/ClassA.php',
        '\kilahm\Random\NS\ClassB' => __DIR__ . '/Fixtures/SubSpace/ClassB.php',
        '\kilahm\RandomOther\NS\ClassC' => __DIR__ . '/Fixtures/SubSpace/ClassC.php',
        '\IfaceNS\MyInterface' => __DIR__ . '/Fixtures/InterfaceA.php',
    };

    public function testScannerFindsAllClasses() : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $this->expect($scan->mapClassToFile())->toEqual($this->allClasses);
    }

    public function testScannerDoesNotFindExcludedDirectories() : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'}, Set{__DIR__ . '/Fixtures/SubSpace/'});
        $this->expect($scan->mapClassToFile())->toEqual($this->topClasses);
    }

    public function testScannerFindsClassesAndInterfaces() : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $this->expect($scan->mapClassOrInterfaceToFile())->toEqual($this->allClassesAndInterfaces);
    }
}
