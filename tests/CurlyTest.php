<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\DefinitionType;
use HackPack\Scanner\FileParser;

// Usually, '{' becomes '{' - however, when used for
// string interpolation, you get a T_CURLY_OPEN.
//
// Interestingly enough, the matching '}' is still just '}' -
// there is no such thing as T_CURLY_CLOSE.
//
// This test makes sure that this doesn't get confused.
<<TestSuite>>
final class CurlyTest
{
    const string DATA_FILE = __DIR__.'/data/code/curly_then_function.php';

    <<Test>>
    public function testDefinitions(Assert $assert) : void
    {
        $p = new FileParser(file_get_contents(self::DATA_FILE));

        $assert
            ->mixed($p->get(DefinitionType::CLASS_DEF))
            ->looselyEquals(Vector{'Foo'});

        $assert
            ->mixed($p->get(DefinitionType::FUNCTION_DEF))
            ->looselyEquals(Vector{'my_func'});
    }

    // Actually testing the tokenizer hasn't changed
    <<Test>>
    public function testContainsTCurlyOpen(Assert $assert) : void
    {
        $matched = false;
        $tokens = token_get_all(file_get_contents(self::DATA_FILE));
        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] === T_CURLY_OPEN) {
                $matched = true;
                break;
            }
        }
        $assert->bool($matched)->is(true);
    }

    // Actually testing the tokenizer hasn't changed
    <<Test>>
    public function testDoesNotContainTCurlyClose(Assert $assert) : void
    {
        $tokens = token_get_all(file_get_contents(self::DATA_FILE));
        foreach ($tokens as $token) {
            if (!is_array($token)) {
                continue;
            }
            $assert->bool($token[1] !== '}')->is(true);
        }
    }
}
