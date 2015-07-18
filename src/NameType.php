<?hh // strict

namespace HackPack\Scanner;

enum NameType: int {
  CLASS_DEF = T_CLASS;
  INTERFACE_DEF = T_INTERFACE;
  TRAIT_DEF = T_TRAIT;
  ENUM_DEF = T_ENUM;
  TYPE_DEF = 403; // facebook/hhvm#4872
  NEWTYPE_DEF = 405; // facebook/hhvm#4872
  FUNCTION_DEF = T_FUNCTION;
  CONST_DEF = T_CONST;
}
