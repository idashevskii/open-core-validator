<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

class All extends ValidationRule {

  protected static ?string $name = 'all';

  public function __construct(array $children) {
    parent::__construct(children: $children);
  }
  
  public function evaluate(mixed $data): ValidationResult {
    return Helpers::evaluateAllChildren($this, $data);
  }
}
