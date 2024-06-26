<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntType extends ValidationRule {

  protected static ?string $name = 'int';

  public function evaluate(mixed $data): ValidationResult {
    if (is_int($data)) {
      $valid = true;
    } else if (is_string($data)) {
      $valid = $data === (string)((int)$data);
    } else {
      $valid = false;
    }
    if (!$valid) {
      return ValidationResult::invalidFor($this);
    }
    return ValidationResult::validFor($this);
  }
}
