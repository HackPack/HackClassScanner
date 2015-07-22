<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\NameType;

<<TestSuite>>
final class MultiNamespacePHPTest extends \PHPUnit_Framework_TestCase implements HasFileName
{
    use FileParserTest;

    public function getFilename() : string
    {
        return 'multi_namespace_php.php';
    }

    <<Test>>
    public function testClasses(): void {
        $expected = Vector {
            'Foo\\Bar',
            'Herp\\Derp',
            'EmptyNamespace',
        };
        $actual = $this->parser->get(NameType::className);
        $this->assertEquals($expected, $actual);
    }
}
