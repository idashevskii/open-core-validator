<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

class Required extends ValidationRule {

  protected static ?string $name = 'required';

  public function __construct(array $children = null) {
    parent::__construct(children: $children);
  }

  public function evaluate(mixed $data): ValidationResult {
    if (Helpers::isEmpty($data)) {
      return ValidationResult::invalidFor($this);
    }
    return Helpers::evaluateAllChildren($this, $data);
  }
}
