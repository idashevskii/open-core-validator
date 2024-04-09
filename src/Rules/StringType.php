<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringType extends ValidationRule {

  protected static ?string $name = 'string';

  public function evaluate(mixed $data): ValidationResult {
    if (!is_string($data)) {
      return ValidationResult::invalidFor($this);
    }
    return ValidationResult::validFor($this);
  }
}
