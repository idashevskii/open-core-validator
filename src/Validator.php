<?php declare(strict_types=1);

namespace OpenCore\Validator;

use OpenCore\Validator\Rules\All;
use OpenCore\Validator\Rules\Key;
use OpenCore\Validator\Rules\Optional;
use OpenCore\Validator\Rules\Required;

class Validator {

  public static function validate(mixed $data, ValidationRule $rule): void {
    $result = $rule->evaluate($data);
    if (!$result->valid) {
      throw new ValidationException($result);
    }
  }

  public static function validateByModel(string $class, mixed $data): void {
    $rule = self::createRuleByModel($class);
    self::validate($data, $rule);
  }

  /**
   * By default, model property is optional, if its type is nullable or it has default value
   */
  public static function createRuleByModel(string $class): ValidationRule {
    $rClass = new \ReflectionClass($class);
    $ret = [];
    foreach ($rClass->getProperties() as $rProp) {
      /** @var \ReflectionProperty $rProp */

      $rules = [];
      foreach ($rProp->getAttributes(ValidationRule::class, flags: \ReflectionAttribute::IS_INSTANCEOF) as $rAttr) {
        $rules[] = $rAttr->newInstance();
      }

      $rType = $rProp->getType();
      $isOptional = $rType->allowsNull() || $rProp->hasDefaultValue();

      if ($rType instanceof \ReflectionNamedType) {
        if (!$rType->isBuiltin()) {
          $rules = array_merge($rules, self::createRuleByModel($rType->getName())->children);
        }
      }

      $wrapperRule = $isOptional ? new Optional($rules) : new Required($rules);
      $ret[] = new Key($rProp->getName(), [$wrapperRule]);
    }
    return new All($ret);
  }

}
