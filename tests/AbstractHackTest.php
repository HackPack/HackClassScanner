<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\Scanner\DefinitionType;

abstract class AbstractHackTest extends \PHPUnit_Framework_TestCase implements HasFileName
{
    use FileParserTest;

    abstract protected function getPrefix(): string;

    <<Test>>
    public function testClasses() : void
    {
        $expected = Vector {
            $this->getPrefix().'SimpleClass',
            $this->getPrefix().'GenericClass',
            $this->getPrefix().'AbstractFinalClass',
            $this->getPrefix().'AbstractClass',
            $this->getPrefix().'xhp_foo',
            $this->getPrefix().'xhp_foo__bar',
        };
        $actual = $this->parser->get(DefinitionType::CLASS_DEF);
    }

    <<Test>>
    public function testTypes(): void {
        $expected = Vector {
            $this->getPrefix().'MyType',
            $this->getPrefix().'MyGenericType',
        };
        $actual = $this->parser->get(DefinitionType::TYPE_DEF);
        $this->assertEquals($expected, $actual);
    }

    <<Test>>
    public function testNewtypes(): void {
        $expected = Vector {
            $this->getPrefix().'MyNewtype',
            $this->getPrefix().'MyGenericNewtype',
        };
        $actual = $this->parser->get(DefinitionType::NEWTYPE_DEF);
        $this->assertEquals($expected, $actual);
    }

    <<Test>>
    public function testEnums(): void {
        $expected = Vector {
            $this->getPrefix().'MyEnum',
        };
        $actual = $this->parser->get(DefinitionType::ENUM_DEF);
        $this->assertEquals($expected, $actual);
    }

    <<Test>>
    public function testFunctions(): void {
        // As well as testing that these functions were mentioned,
        // this also checks that SimpelClass::iAmNotAGlobalFunction
        // was not listed
        $expected = Vector {
            $this->getPrefix().'simple_function',
            $this->getPrefix().'generic_function',
            $this->getPrefix().'byref_return_function',
        };
        $actual = $this->parser->get(DefinitionType::FUNCTION_DEF);
        $this->assertEquals($expected, $actual);
    }

    <<Test>>
    public function testConstants(): void {
        // Makes sure that GenericClass::NOT_A_GLOBAL_CONSTANT is not returned
        $expected = Vector {
            $this->getPrefix().'MY_CONST',
            $this->getPrefix().'MY_TYPED_CONST',
            $this->getPrefix().'MY_OLD_STYLE_CONST',
            $this->getPrefix().'MY_OTHER_OLD_STYLE_CONST',
            $this->getPrefix().'NOW_IM_JUST_FUCKING_WITH_YOU',
        };
        $actual = $this->parser->get(DefinitionType::CONST_DEF);
        $this->assertEquals($expected, $actual);
    }
}
