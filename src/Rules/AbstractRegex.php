<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

abstract class AbstractRegex extends ValidationRule {

  abstract protected function getRegex(): string;

  public function evaluate(mixed $data): ValidationResult {
    $valid = is_string($data) && preg_match($this->getRegex(), $data);
    return new ValidationResult($valid, $this);
  }
}
