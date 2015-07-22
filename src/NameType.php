<?hh // strict

namespace HackPack\Scanner;

enum NameType: int {
  className = T_CLASS;
  interfaceName = T_INTERFACE;
  traitName = T_TRAIT;
  enumName = T_ENUM;
  typeName = 403; // facebook/hhvm#4872
  newtypeName = 405; // facebook/hhvm#4872
  functionName = T_FUNCTION;
  constantName = T_CONST;
}
