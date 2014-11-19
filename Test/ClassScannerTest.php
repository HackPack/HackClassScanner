<?hh // strict

namespace kilahm\Test;

use kilahm\Scanner\ClassScanner;
use HackPack\HackUnit\Core\TestCase;

class ClassScannerTest extends TestCase
{
    private Map<string,string> $allClasses = Map{
        '\ClassA' => '/home/vagrant/lib/HackClassScanner/Test/Fixtures/ClassA.php',
        '\kilahm\Random\NS\ClassB' => '/home/vagrant/lib/HackClassScanner/Test/Fixtures/SubSpace/ClassB.php',
        '\kilahm\RandomOther\NS\ClassC' => '/home/vagrant/lib/HackClassScanner/Test/Fixtures/SubSpace/ClassC.php',
    };

    private Map<string,string> $topClasses = Map{
        '\ClassA' => '/home/vagrant/lib/HackClassScanner/Test/Fixtures/ClassA.php',
    };

    public function testScannerFindsAllClasses() : void
    {
        chdir(__DIR__);
        $scan = new ClassScanner(Set{'Fixtures/'});
        $this->expect($scan->mapFileToClass())->toEqual($this->allClasses);
    }

    public function testScannerDoesNotFindExcludedDirectories() : void
    {
        chdir(__DIR__);
        $scan = new ClassScanner(Set{'Fixtures/'}, Set{'Fixtures/SubSpace/'});
        $this->expect($scan->mapFileToClass())->toEqual($this->topClasses);
    }
}
