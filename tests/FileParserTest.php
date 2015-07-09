<?hh // strict

namespace HackPack\Scanner\Tests;

use HackPack\Scanner\FileParser;

trait FileParserTest
{
    require implements HasFileName;

    private FileParser $parser;

    public function __construct()
    {
        $this->parser = new FileParser('');
    }

    <<Setup>>
    public function setUp() : void
    {
        $this->parser = new FileParser(
            file_get_contents(__DIR__.'/data/code/'.$this->getFilename()),
        );
    }
}
