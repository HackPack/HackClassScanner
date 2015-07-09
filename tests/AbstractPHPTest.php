<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\DefinitionType;
use HackPack\Scanner\FileParser;

abstract class AbstractPHPTest implements HasFileName
{
    use FileParserTest;

    abstract protected function getPrefix(): string;

    <<Test>>
    public function testClasses(Assert $assert): void {
        $expected = Vector {
            $this->getPrefix().'SimpleClass',
            $this->getPrefix().'SimpleAbstractClass',
            $this->getPrefix().'SimpleFinalClass',
        };
        $actual = $this->parser->get(DefinitionType::CLASS_DEF);
        $assert->mixed($actual)->looselyEquals($expected);
    }

    <<Test>>
    public function testInterfaces(Assert $assert): void {
        $expected = Vector{
            $this->getPrefix().'SimpleInterface',
        };
        $actual = $this->parser->get(DefinitionType::INTERFACE_DEF);
        $assert->mixed($actual)->looselyEquals($expected);
    }

    <<Test>>
    public function testTraits(Assert $assert): void {
        $expected = Vector{
            $this->getPrefix().'SimpleTrait'
        };
        $actual = $this->parser->get(DefinitionType::TRAIT_DEF);
        $assert->mixed($actual)->looselyEquals($expected);
    }
}
