<?hh // strict

namespace HackPack\Scanner;

interface DefinitionFinder
{
    public function get(DefinitionType $type) : \ConstVector<string>;
}
