<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\Scanner\NameType;
use HackPack\Scanner\FileParser;

abstract class AbstractPHPTest extends \PHPUnit_Framework_TestCase implements HasFileName
{
    use FileParserTest;

    abstract protected function getPrefix(): string;

    <<Test>>
    public function testClasses(): void {
        $expected = Vector {
            $this->getPrefix().'SimpleClass',
            $this->getPrefix().'SimpleAbstractClass',
            $this->getPrefix().'SimpleFinalClass',
        };
        $actual = $this->parser->get(NameType::className);
        $this->assertEquals($expected, $actual);
    }

    <<Test>>
    public function testInterfaces(): void {
        $expected = Vector{
            $this->getPrefix().'SimpleInterface',
        };
        $actual = $this->parser->get(NameType::interfaceName);
        $this->assertEquals($expected, $actual);
    }

    <<Test>>
    public function testTraits(): void {
        $expected = Vector{
            $this->getPrefix().'SimpleTrait'
        };
        $actual = $this->parser->get(NameType::traitName);
        $this->assertEquals($expected, $actual);
    }
}
