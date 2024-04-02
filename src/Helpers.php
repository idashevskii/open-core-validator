<?php declare(strict_types=1);

namespace OpenCore\Validator;

final class Helpers {

  public static function isEmpty(mixed $data) {
    return $data === null;
  }

  public static function validateValueBetween(ValidationRule $rule, mixed $value) {
    ['min' => $min, 'max' => $max] = $rule->options;
    $valid = ($value >= $min) && ($value <= $max);
    return new ValidationResult($valid, $rule);
  }

  public static function evaluateAllChildren(ValidationRule $rule, mixed $data): ValidationResult {
    if (!$rule->children) {
      return ValidationResult::validFor($rule);
    }
    $valid = true;
    $childResults = [];
    foreach ($rule->children as $childRule) {
      $childRes = $childRule->evaluate($data);
      if (!$childRes->valid) {
        $valid = false;
      }
      $childResults[] = $childRes;
    }
    return new ValidationResult($valid, $rule, children: $childResults);
  }

}
