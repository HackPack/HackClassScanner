<?hh // strict

namespace HackPack\Scanner;

interface DefinitionFinder
{
    public function get(NameType $type) : \ConstVector<string>;
}
