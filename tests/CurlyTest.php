<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\HackUnit\Contract\Assert;
use HackPack\Scanner\NameType;
use HackPack\Scanner\FileParser;

// Usually, '{' becomes '{' - however, when used for
// string interpolation, you get a T_CURLY_OPEN.
//
// Interestingly enough, the matching '}' is still just '}' -
// there is no such thing as T_CURLY_CLOSE.
//
// This test makes sure that this doesn't get confused.
<<TestSuite>>
final class CurlyTest extends \PHPUnit_Framework_TestCase
{
    const string DATA_FILE = __DIR__.'/data/code/curly_then_function.php';

    <<Test>>
    public function testDefinitions() : void
    {
        $p = new FileParser(file_get_contents(self::DATA_FILE));

        $this->assertEquals(Vector{'Foo'}, $p->get(NameType::className));
        $this->assertEquals(Vector{'my_func'}, $p->get(NameType::functionName));
    }

    // Actually testing the tokenizer hasn't changed
    <<Test>>
    public function testContainsTCurlyOpen() : void
    {
        $matched = false;
        $tokens = token_get_all(file_get_contents(self::DATA_FILE));
        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] === T_CURLY_OPEN) {
                $matched = true;
                break;
            }
        }
        $this->assertTrue($matched);
    }

    // Actually testing the tokenizer hasn't changed
    <<Test>>
    public function testDoesNotContainTCurlyClose() : void
    {
        $tokens = token_get_all(file_get_contents(self::DATA_FILE));
        foreach ($tokens as $token) {
            if (!is_array($token)) {
                continue;
            }
            $this->assertTrue($token[1] !== '}');
        }
    }
}
