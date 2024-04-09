<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BoolType extends ValidationRule {

  protected static ?string $name = 'bool';

  public function evaluate(mixed $data): ValidationResult {
    $valid = is_bool($data) || $data === '1' || $data === '';
    if (!$valid) {
      return ValidationResult::invalidFor($this);
    }
    return ValidationResult::validFor($this);
  }
}
