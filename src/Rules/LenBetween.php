<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use Attribute;
use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class LenBetween extends ValidationRule {

  protected static ?string $name = 'lenBetween';

  public function __construct(int $min, int $max) {
    parent::__construct(options: ['min' => $min, 'max' => $max]);
  }

  public function evaluate(mixed $data): ValidationResult {
    if (!is_string($data)) {
      return ValidationResult::invalidFor($this);
    }
    return Helpers::validateValueBetween($this, mb_strlen($data));
  }
}
