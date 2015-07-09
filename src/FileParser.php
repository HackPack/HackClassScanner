<?hh // strict

namespace HackPack\Scanner;

class FileParser implements DefinitionFinder
{
  private array<mixed> $tokens = [];
  private string $namespace = '';
  private Vector<string> $classes = Vector { };
  private Vector<string> $interfaces = Vector { };
  private Vector<string> $traits = Vector { };
  private Vector<string> $enums = Vector { };
  private Vector<string> $types = Vector { };
  private Vector<string> $newtypes = Vector { };
  private Vector<string> $functions = Vector { };
  private Vector<string> $constants = Vector { };

  public function __construct(
    private string $data,
    private string $file = '__DATA__',
  ) {
    $this->consumeFile();
  }

  public function setFileName(string $name) : void
  {
      $this->file = $name;
  }

  public function get(DefinitionType $type) : \ConstVector<string>
  {
      switch($type) {
      case DefinitionType::CLASS_DEF:
          return $this->classes;
      case DefinitionType::INTERFACE_DEF:
          return $this->interfaces;
      case DefinitionType::TRAIT_DEF:
          return $this->traits;
      case DefinitionType::ENUM_DEF :
          return $this->enums;
      case DefinitionType::TYPE_DEF :
          return $this->types;
      case DefinitionType::NEWTYPE_DEF :
          return $this->newtypes;
      case DefinitionType::FUNCTION_DEF :
          return $this->functions;
      case DefinitionType::CONST_DEF :
          return $this->constants;
      }
  }

  ///// Implementation /////

  private function consumeFile(): void {
    $data = $this->data;
    invariant(
      $data !== null,
      'somehow got constructed with null data for %s',
      $this->file,
    );
    $this->tokens = token_get_all($data);

    $parens_depth = 0;
    while ($this->tokens) {
      $this->skipToCode();
      while ($this->tokens) {
        list ($token, $ttype) = $this->shiftToken();
        if ($token === '(') {
          ++$parens_depth;
        }
        if ($token === ')') {
          --$parens_depth;
        }

        if ($parens_depth !== 0 || $ttype === null) {
          continue;
        }

        if ($ttype === T_CLOSE_TAG) {
          break;
        }

        if ($ttype === T_NAMESPACE) {
            $this->consumeWhitespace();
            $this->consumeNamespaceDefinition();
        }

        if (DefinitionType::isValid($ttype)) {
          $this->consumeDefinition(DefinitionType::assert($ttype));
          continue;
        }
        // I hate you, PHP.
        if ($ttype === T_STRING && strtolower($token) === 'define') {
          $this->consumeOldConstantDefinition();
          continue;
        }
      }
    }
    $this->data = '';
  }

  private function skipToCode(): void {
    $token_type = null;
    do {
      list ($token, $token_type) = $this->shiftToken();
    } while ($this->tokens && $token_type !== T_OPEN_TAG);
  }

  private function consumeDefinition(DefinitionType $def_type): void {
    $this->consumeWhitespace();

    switch ($def_type) {
      case DefinitionType::CLASS_DEF:
      case DefinitionType::INTERFACE_DEF:
      case DefinitionType::TRAIT_DEF:
        $this->consumeClassDefinition($def_type);
        return;
      case DefinitionType::FUNCTION_DEF:
        $this->consumeFunctionDefinition();
        return;
      case DefinitionType::CONST_DEF:
        $this->consumeConstantDefinition();
        return;
      case DefinitionType::TYPE_DEF:
      case DefinitionType::NEWTYPE_DEF:
      case DefinitionType::ENUM_DEF:
        $this->consumeSimpleDefinition($def_type);
        return;
    }
  }

  /**
   * /const CONST_NAME =/
   * /const type_name CONST_NAME =/
   */
  private function consumeConstantDefinition(): void {
    $name = null;
    while ($this->tokens) {
      list ($next, $next_type) = $this->shiftToken();
      if ($next_type === T_WHITESPACE) {
        continue;
      }
      if ($next_type === T_STRING) {
        $name = $next;
        continue;
      }
      if ($next === '=') {
        $this->constants[] = $this->namespace.$name;
        return;
      }
    }
    $this->consumeStatement();
  }

  /**
   * define ('FOO', value);
   * define (FOO, value); // yep, this is different. I *REALLY* hate php.
   *
   * 'define' has been consumed, that's it
   */
  private function consumeOldConstantDefinition(): void {
    $this->consumeWhitespace();
    $next = array_shift($this->tokens);
    invariant(
      $next === '(',
      'Expected define to be followed by a paren in %s',
      $this->file,
    );
    $this->consumeWhitespace();
    list ($next, $next_type) = $this->shiftToken();
    invariant(
      $next_type === T_CONSTANT_ENCAPSED_STRING || $next_type === T_STRING,
      'Expected arg to define() to be a T_CONSTANT_ENCAPSED_STRING or '.
      'T_STRING, got %s in %s',
      token_name($next_type),
      $this->file,
    );
    $name = $next;
    if ($next_type === T_STRING) {
      // CONST_NAME
      $this->constants[] = $this->namespace.$name;
    } else {
      // 'CONST_NAME' or "CONST_NAME"
      invariant(
        $name[0] == $name[strlen($name) - 1],
        'Mismatched quotes',
      );
      $this->constants[] = $this->namespace.
        substr($name, 1, strlen($name) - 2);
    }
    $this->consumeStatement();
  }

  private function consumeWhitespace(): void {
    $next = array_shift($this->tokens);
    if (is_array($next) && $next[0] === T_WHITESPACE) {
      return;
    }
    array_unshift($this->tokens, $next);
  }

  private function consumeNamespaceDefinition(): void {
    $parts = [];
    do {
      $this->consumeWhitespace();
      list($next, $next_type) = $this->shiftToken();
      if ($next_type === T_STRING) {
        $parts[] = $next;
        continue;
      } else if ($next_type === T_NS_SEPARATOR) {
        continue;
      } else if ($next === '{' || $next === ';') {
        break;
      }
      invariant_violation(
        'Unexpected token %s in %s',
        var_export($next, true),
        $this->file,
      );
    } while ($this->tokens);

    if ($parts) {
      $this->namespace = implode('\\', $parts).'\\';
    } else {
      $this->namespace = '';
    }
  }

  private function skipToAndConsumeBlock(): void {
    $nesting = 0;
    while ($this->tokens) {
      list($next, $next_type) = $this->shiftToken();
      if ($next === '{' || $next_type === T_CURLY_OPEN) {
        ++$nesting;
      } else if ($next === '}') { // no such thing as T_CURLY_CLOSE
        --$nesting;
        if ($nesting === 0) {
          return;
        }
      }
    }
  }

  private function consumeStatement(): void {
    while ($this->tokens) {
      $next = array_shift($this->tokens);
      if ($next === ';') {
        return;
      }
      if ($next === '{') {
        array_unshift($this->tokens, $next);
        $this->skipToAndConsumeBlock();
        return;
      }
    }
  }

  private function shiftToken(): (string, ?int) {
    $token = array_shift($this->tokens);
    if (is_array($token)) {
      return tuple($token[1], $token[0]);
    }
    return tuple($token, null);
  }

  private function consumeClassDefinition(DefinitionType $def_type): void {
    list($v, $t) = $this->shiftToken();
    if ($t === T_STRING) {
      $name = $v;
    } else {
      invariant(
        $t === T_XHP_LABEL,
        'Unknown class token %d in %s',
        token_name($t),
        $this->file,
      );
      invariant(
        $def_type === DefinitionType::CLASS_DEF,
        'Seeing an XHP class name for a %s in %s',
        token_name($def_type),
        $this->file,
      );
      // 'class :foo:bar' is really 'class xhp_foo__bar'
      $name = 'xhp_'.str_replace(':', '__', substr($v, 1));
    }
    $fqn = $this->namespace.$name;
    switch ($def_type) {
      case DefinitionType::CLASS_DEF:
        $this->classes[] = $fqn;
        break;
      case DefinitionType::INTERFACE_DEF:
        $this->interfaces[] = $fqn;
        break;
      case DefinitionType::TRAIT_DEF:
        $this->traits[] = $fqn;
        break;
      default:
        invariant_violation(
          'Trying to define %s as a class',
          token_name($def_type),
        );
    }
    $this->skipToAndConsumeBlock();
  }

  private function consumeSimpleDefinition(DefinitionType $def_type): void {
    list($next, $next_type) = $this->shiftToken();
    invariant(
      $next_type === T_STRING,
      'Expected a string for %s, got %d - in %s',
      token_name($def_type),
      $next_type,
      $this->file,
    );
    $fqn = $this->namespace.$next;
    switch ($def_type) {
      case DefinitionType::TYPE_DEF:
        $this->types[] = $fqn;
        break;
      case DefinitionType::NEWTYPE_DEF:
        $this->newtypes[] = $fqn;
        break;
      case DefinitionType::ENUM_DEF:
        $this->enums[] = $fqn;
        $this->skipToAndConsumeBlock();
        return;
      default:
        invariant_violation(
          '%d is not a simple definition',
          $def_type,
        );
    }
    $this->consumeStatement();
  }

  private function consumeFunctionDefinition(): void {
    list($next, $next_type) = $this->shiftToken();
    if ($next === '&') {
      // byref return
      $this->consumeWhitespace();
      list($next, $next_type) = $this->shiftToken();
    }
    if ($next === '(') {
      // rvalue
      return;
    }
    invariant(
      $next_type === T_STRING,
      'Expected a function name in %s',
      $this->file,
    );
    $this->functions[] = $this->namespace.$next;
  }
}
