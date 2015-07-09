<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\DefinitionType;
use HackPack\Scanner\FileParser;

/**
 * 'function' is a valid keyword in several contexts other than when definining
 * a function; make sure they're not considered a function.
 */
<<TestSuite>>
final class FunctionNotDefinitionTest
{
    <<Test>>
    public function testActuallyAFunction(Assert $assert) : void {
        $p = new FileParser('<?hh function foo();');
        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{'foo'});
    }

    <<Test>>
    public function testFunctionTypeAlias(Assert $assert) : void {
        $p = new FileParser('<?hh newtype Foo = function(int): void;');
        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{});
        $assert
            ->mixed($p->get(DefinitionType::NEWTYPE_DEF))
            ->looselyEquals(Vector{'Foo'});

        // Add extra whitespace
        $p = new FileParser('<?hh newtype Foo = function (int): void;');
        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{});
        $assert
            ->mixed($p->get(DefinitionType::NEWTYPE_DEF))
            ->looselyEquals(Vector{'Foo'});
    }

    <<Test>>
    public function testFunctionReturnType(Assert $assert) : void {
        $p = new FileParser(<<<EOF
<?hh
function foo(\$bar): (function():void) { return \$bar; }
EOF
    );
        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{'foo'});
    }

    <<Test>>
    public function testAsParameterType(Assert $assert) : void {
        $p = new FileParser('<?hh function foo((function():void) $callback) { }');
        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{'foo'});
    }

    <<Test>>
    public function testUsingAnonymousFunctions(Assert $assert) : void {
        $p = new FileParser(<<<EOF
<?hh
function foo() {
  \$x = function() { return 'bar'; };
  return \$x();
}
EOF
    );
        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{'foo'});
    }

    <<Test>>
    public function testAsParameter(Assert $assert) : void {
        $p = new FileParser(<<<EOF
<?php
spl_autoload_register(function(\$class) { });
function foo() { }
EOF
    );
        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{'foo'});
    }

    <<Test>>
    public function testAsRVal(Assert $assert) : void {
        $p = new FileParser('<?php $f = function(){};');
        $assert
            ->bool($p->get(DefinitionType::FUNCTION_DEF)->isEmpty())
            ->is(true);
    }
}
