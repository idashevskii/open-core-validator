<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

class Any extends ValidationRule {

  protected static ?string $name = 'any';

  public function __construct(array $children) {
    parent::__construct(children: $children);
  }

  public function evaluate(mixed $data): ValidationResult {
    $valid = false;
    $childResults = [];
    foreach ($this->children as $childRule) {
      $childRes = $childRule->evaluate($data);
      if ($childRes->valid) {
        $valid = true;
      }
      $childResults[] = $childRes;
    }
    return new ValidationResult($valid, $this, children: $childResults);
  }
}
