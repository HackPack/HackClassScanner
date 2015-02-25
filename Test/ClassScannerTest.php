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
        '\kilahm\Test\In\With\More\In\Parts\In\In' => __DIR__ . '/Fixtures/SubSpace/ClassIn.php',
        '\TextClass' => __DIR__ . '/Fixtures/text.txt',
    };

    private Map<string,string> $topClasses = Map{
        '\ClassA' => __DIR__ . '/Fixtures/ClassA.php',
        '\TextClass' => __DIR__ . '/Fixtures/text.txt',
    };

    private Map<string,string> $allClassesAndInterfaces = Map{
        '\ClassA' => __DIR__ . '/Fixtures/ClassA.php',
        '\IfaceNS\MyInterface' => __DIR__ . '/Fixtures/InterfaceA.php',
        '\kilahm\Random\NS\ClassB' => __DIR__ . '/Fixtures/SubSpace/ClassB.php',
        '\kilahm\RandomOther\NS\ClassC' => __DIR__ . '/Fixtures/SubSpace/ClassC.php',
        '\kilahm\Test\In\With\More\In\Parts\In\In' => __DIR__ . '/Fixtures/SubSpace/ClassIn.php',
        '\TextClass' => __DIR__ . '/Fixtures/text.txt',
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

    public function testClassFilter() : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $scan->addClassNameFilter($className ==> $className === '\ClassA');
        $this->expect($scan->mapClassToFile())->toEqual(Map{'\ClassA' => __DIR__ . '/Fixtures/ClassA.php'});
    }

    public function testFileFilter() : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $scan->addFileNameFilter($fileName ==> $fileName === __DIR__ . '/Fixtures/ClassA.php');
        $this->expect($scan->mapClassToFile())->toEqual(Map{'\ClassA' => __DIR__ . '/Fixtures/ClassA.php'});
    }

    public function testScannerFindsOneFile() : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/text.txt'});
        $this->expect($scan->mapClassToFile())->toEqual(Map{'\TextClass' => __DIR__ . '/Fixtures/text.txt'});
    }

    public function testScannerExcludesOneFile() : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures'}, Set{
            __DIR__ . '/Fixtures/SubSpace',
            __DIR__ . '/Fixtures/text.txt'
        });
        $this->expect($scan->mapClassToFile())->toEqual(Map{'\ClassA' => __DIR__ . '/Fixtures/ClassA.php'});
    }
}
