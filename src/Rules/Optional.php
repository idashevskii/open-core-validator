<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

class Optional extends ValidationRule {

  protected static ?string $name = 'optional';

  public function __construct(array $children = null) {
    parent::__construct(children: $children);
  }

  public function evaluate(mixed $data): ValidationResult {
    if (Helpers::isEmpty($data)) {
      return ValidationResult::validFor($this);
    }
    return Helpers::evaluateAllChildren($this, $data);
  }
}
