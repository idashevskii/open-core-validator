<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

class Prop extends ValidationRule {

  protected static ?string $name = 'prop';

  public function __construct(string|int $prop, array $children) {
    parent::__construct(options: ['prop' => $prop], children: $children);
  }

  public function getIndex(): string|int|null {
    return $this->options['prop'];
  }

  public function evaluate(mixed $data): ValidationResult {
    ['prop' => $prop] = $this->options;
    if (!is_object($data) || !property_exists($data, $prop)) {
      return ValidationResult::invalidFor($this);
    }
    return Helpers::evaluateAllChildren($this, $data->$prop);
  }
}
