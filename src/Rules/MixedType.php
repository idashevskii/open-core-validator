<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MixedType extends ValidationRule {

  protected static ?string $name = 'mixed';

  public function evaluate(mixed $data): ValidationResult {
    return ValidationResult::validFor($this);
  }
}
