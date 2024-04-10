<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Between extends ValidationRule {

  protected static ?string $name = 'between';

  public function __construct(float|int $min, float|int $max) {
    parent::__construct(options: ['min' => $min, 'max' => $max]);
  }

  public function getDetails(): array|null {
    return $this->options;
  }

  public function evaluate(mixed $data): ValidationResult {
    if (!is_numeric($data)) {
      return ValidationResult::invalidFor($this);
    }
    return Helpers::validateValueBetween($this, $data + 0);
  }
}
