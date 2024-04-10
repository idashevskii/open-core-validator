<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CountBetween extends ValidationRule {

  protected static ?string $name = 'countBetween';

  public function __construct(int $min, int $max) {
    parent::__construct(options: ['min' => $min, 'max' => $max]);
  }

  public function getDetails(): array|null {
    return $this->options;
  }

  public function evaluate(mixed $data): ValidationResult {
    if (!is_array($data)) {
      return ValidationResult::invalidFor($this);
    }
    return Helpers::validateValueBetween($this, count($data));
  }
}
