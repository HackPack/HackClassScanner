<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\NameType;
use HackPack\Scanner\FileParser;

/**
 * 'function' is a valid keyword in several contexts other than when definining
 * a function; make sure they're not considered a function definition.
 */
<<TestSuite>>
final class FunctionNotDefinitionTest extends \PHPUnit_Framework_TestCase
{
    <<Test>>
    public function testActuallyAFunction() : void {
        $p = new FileParser('<?hh function foo();');
        $this->assertEquals(
            Vector{'foo'},
            $p->get(NameType::FUNCTION_DEF),
        );
    }

    <<Test>>
    public function testFunctionTypeAlias() : void {
        $p = new FileParser('<?hh newtype Foo = function(int): void;');
        $this->assertEquals(
            Vector{},
            $p->get(NameType::FUNCTION_DEF),
        );
        $this->assertEquals(
            Vector{'Foo'},
            $p->get(NameType::NEWTYPE_DEF),
        );

        // Add extra whitespace
        $p = new FileParser('<?hh newtype Foo = function (int): void;');
        $this->assertEquals(
            Vector{},
            $p->get(NameType::FUNCTION_DEF),
        );
        $this->assertEquals(
            Vector{'Foo'},
            $p->get(NameType::NEWTYPE_DEF),
        );
    }

    <<Test>>
    public function testFunctionReturnType() : void {
        $p = new FileParser(<<<EOF
<?hh
function foo(\$bar): (function():void) { return \$bar; }
EOF
    );
        $this->assertEquals(
            Vector{'foo'},
            $p->get(NameType::FUNCTION_DEF),
        );
    }

    <<Test>>
    public function testAsParameterType() : void {
        $p = new FileParser('<?hh function foo((function():void) $callback) { }');
        $this->assertEquals(
            Vector{'foo'},
            $p->get(NameType::FUNCTION_DEF),
        );
    }

    <<Test>>
    public function testUsingAnonymousFunctions() : void {
        $p = new FileParser(<<<EOF
<?hh
function foo() {
  \$x = function() { return 'bar'; };
  return \$x();
}
EOF
    );
        $this->assertEquals(
            Vector{'foo'},
            $p->get(NameType::FUNCTION_DEF),
        );
    }

    <<Test>>
    public function testAsParameter() : void {
        $p = new FileParser(<<<EOF
<?php
spl_autoload_register(function(\$class) { });
function foo() { }
EOF
    );
        $this->assertEquals(
            Vector{'foo'},
            $p->get(NameType::FUNCTION_DEF),
        );
    }

    <<Test>>
    public function testAsRVal() : void {
        $p = new FileParser('<?php $f = function(){};');
        $this->assertTrue($p->get(NameType::FUNCTION_DEF)->isEmpty());
    }
}
