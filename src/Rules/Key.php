<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use OpenCore\Validator\Helpers;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\ValidationRule;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Key extends ValidationRule {

  protected static ?string $name = 'key';

  public function __construct(string|int $key, array $children) {
    parent::__construct(options: ['key' => $key], children: $children);
  }

  public function getIndex(): string|int|null {
    return $this->options['key'];
  }

  public function evaluate(mixed $data): ValidationResult {
    ['key' => $key] = $this->options;
    if (!is_array($data)) {
      return ValidationResult::invalidFor($this);
    }
    return Helpers::evaluateAllChildren($this, $data[$key] ?? null);
  }
}
