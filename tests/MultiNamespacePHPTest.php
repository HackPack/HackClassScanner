<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\DefinitionType;

<<TestSuite>>
final class MultiNamespacePHPTest implements HasFileName
{
    use FileParserTest;

    public function getFilename() : string
    {
        return 'multi_namespace_php.php';
    }

    <<Test>>
    public function testClasses(Assert $assert): void {
        $expected = Vector {
            'Foo\\Bar',
            'Herp\\Derp',
            'EmptyNamespace',
        };
        $actual = $this->parser->get(DefinitionType::CLASS_DEF);
        $assert->mixed($actual)->looselyEquals($expected);
    }
}
