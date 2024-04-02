<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Each extends ValidationRule {

  protected static ?string $name = 'each';

  public function __construct(array $children = null) {
    parent::__construct(children: $children);
  }

  public function evaluate(mixed $data): ValidationResult {
    if (!is_array($data)) {
      return ValidationResult::invalidFor($this);
    }
    $childResults = [];
    $valid = true;
    foreach(array_keys($data) as $key){
      $keyRule = new Key($key, $this->children);
      $childRes = $keyRule->evaluate($data);
      if (!$childRes->valid) {
        $valid = false;
      }
      $childResults[] = $childRes;
    }
    return new ValidationResult($valid, $this, children: $childResults);
  }
}
