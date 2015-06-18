<?hh // strict

namespace HackPack\Scanner\Test;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\ClassScanner;

<<TestSuite>>
class ClassScannerTest
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

    <<Test>>
    public function testScannerFindsAllClasses(Assert $assert) : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $assert->mixed($scan->mapClassToFile())->looselyEquals($this->allClasses);
    }

    <<Test>>
    public function testScannerDoesNotFindExcludedDirectories(Assert $assert) : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'}, Set{__DIR__ . '/Fixtures/SubSpace/'});
        $assert->mixed($scan->mapClassToFile())->looselyEquals($this->topClasses);
    }

    <<Test>>
    public function testScannerFindsClassesAndInterfaces(Assert $assert) : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $assert->mixed($scan->mapClassOrInterfaceToFile())->looselyEquals($this->allClassesAndInterfaces);
    }

    <<Test>>
    public function testClassFilter(Assert $assert) : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $scan->addClassNameFilter($className ==> $className === '\ClassA');
        $assert->mixed($scan->mapClassToFile())->looselyEquals(Map{'\ClassA' => __DIR__ . '/Fixtures/ClassA.php'});
    }

    <<Test>>
    public function testFileFilter(Assert $assert) : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/'});
        $scan->addFileNameFilter($fileName ==> $fileName === __DIR__ . '/Fixtures/ClassA.php');
        $assert->mixed($scan->mapClassToFile())->looselyEquals(Map{'\ClassA' => __DIR__ . '/Fixtures/ClassA.php'});
    }

    <<Test>>
    public function testScannerFindsOneFile(Assert $assert) : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures/text.txt'});
        $assert->mixed($scan->mapClassToFile())->looselyEquals(Map{'\TextClass' => __DIR__ . '/Fixtures/text.txt'});
    }

    <<Test>>
    public function testScannerExcludesOneFile(Assert $assert) : void
    {
        $scan = new ClassScanner(Set{__DIR__ . '/Fixtures'}, Set{
            __DIR__ . '/Fixtures/SubSpace',
            __DIR__ . '/Fixtures/text.txt'
        });
        $assert->mixed($scan->mapClassToFile())->looselyEquals(Map{'\ClassA' => __DIR__ . '/Fixtures/ClassA.php'});
    }
}
