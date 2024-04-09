<?php declare(strict_types=1);

namespace OpenCore\Validator;

use OpenCore\Validator\Cast\ArrayList;
use OpenCore\Validator\Rules\All;
use OpenCore\Validator\Rules\BoolType;
use OpenCore\Validator\Rules\Each;
use OpenCore\Validator\Rules\FloatType;
use OpenCore\Validator\Rules\IntType;
use OpenCore\Validator\Rules\Key;
use OpenCore\Validator\Rules\MixedType;
use OpenCore\Validator\Rules\Optional;
use OpenCore\Validator\Rules\Required;
use OpenCore\Validator\Rules\StringType;

class Validator {

  public static function deserialize(string $class, mixed $data): mixed {
    self::validateByModel($class, $data);
    return self::deserializeByModel($class, $data);
  }

  private static function deserializeByModel(string $class, mixed $data): mixed {
    if (Arr::isArr($class)) {
      $subtype = Arr::subtypeFromClass($class);
      $ret = [];
      foreach ($data as $k => $v) {
        $ret[$k] = self::deserializeByModel($subtype, $v);
      }
      return $ret;
    } else if ($class === Type::STRING) {
      return (string) $data;
    } else if ($class === Type::BOOL) {
      return (bool) $data;
    } else if ($class === Type::INT) {
      return (int) $data;
    } else if ($class === Type::FLOAT) {
      return (float) $data;
    } else if ($class === Type::MIXED) {
      return $data;
    }

    $rClass = new \ReflectionClass($class);
    $ret = $rClass->newInstanceWithoutConstructor();
    foreach ($rClass->getProperties() as $rProp) {
      /** @var \ReflectionProperty $rProp */
      /** @var \ReflectionNamedType $rType */
      $rType = $rProp->getType();
      $propName = $rProp->getName();
      $propType = $rType->getName();
      $propValue = $data[$propName] ?? ($rProp->hasDefaultValue() ? $rProp->getDefaultValue() : null);
      if ($propValue === null) {
        // nothing to do with null
      } else if ($propType === Arr::TYPE) {
        [$rArrAttr] = $rProp->getAttributes(Arr::class);
        $propValue = self::deserializeByModel($rArrAttr->newInstance()->toClass(), $propValue);
      } else {
        $propValue = self::deserializeByModel($propType, $propValue);
      }
      $rProp->setValue($ret, $propValue);
    }
    return $ret;
  }

  public static function validate(ValidationRule $rule, mixed $data): void {
    $result = $rule->evaluate($data);
    if (!$result->valid) {
      throw new ValidationException($result);
    }
  }

  public static function validateByModel(string $class, mixed $data): void {
    $rule = self::createRuleByModel($class);
    self::validate($rule, $data);
  }

  /**
   * By default, model property is optional, if its type is nullable or it has default value
   */
  public static function createRuleByModel(string $class): ValidationRule {
    if (Arr::isArr($class)) {
      $subtype = Arr::subtypeFromClass($class);
      return new Each(self::unwrapAllRule(self::createRuleByModel($subtype)));
    } else if ($class === Type::STRING) {
      return new StringType();
    } else if ($class === Type::BOOL) {
      return new BoolType();
    } else if ($class === Type::INT) {
      return new IntType();
    } else if ($class === Type::FLOAT) {
      return new FloatType();
    } else if ($class === Type::MIXED) {
      return new MixedType();
    }

    $rClass = new \ReflectionClass($class);
    $ret = [];
    foreach ($rClass->getProperties() as $rProp) {
      /** @var \ReflectionProperty $rProp */

      $rType = $rProp->getType();
      $propName = $rProp->getName();
      $isOptional = $rType->allowsNull() || $rProp->hasDefaultValue();

      if (!($rType instanceof \ReflectionNamedType)) {
        throw new \ErrorException("Property $propName must have type");
      }

      $propType = $rType->getName();
      if (!$rType->isBuiltin()) {
        $propRule = self::createRuleByModel($propType);
      } else if ($propType === Arr::TYPE) {
        [$rArrAttr] = $rProp->getAttributes(Arr::class) ?: null;
        if (!$rArrAttr) {
          throw new \ErrorException("Property $propName is array and so it must have subtype");
        }
        $propRule = self::createRuleByModel($rArrAttr->newInstance()->toClass());
      } else {
        $propRule = self::createRuleByModel($propType);
      }
      $rules = self::unwrapAllRule($propRule);

      foreach ($rProp->getAttributes(ValidationRule::class, flags: \ReflectionAttribute::IS_INSTANCEOF) as $rAttr) {
        $rules[] = $rAttr->newInstance();
      }

      $wrapperRule = $isOptional ? new Optional($rules) : new Required($rules);
      $ret[] = new Key($propName, [$wrapperRule]);
    }
    return new All($ret);
  }

  private static function unwrapAllRule(ValidationRule $rule) {
    return $rule instanceof All ? $rule->children : [$rule];
  }

}
