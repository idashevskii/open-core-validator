<?php declare(strict_types=1);

namespace OpenCore\Validator;

final class ValidationResult {

  public function __construct(
    public readonly bool $valid,
    public readonly ValidationRule $rule,
    public readonly ?array $children = null,
  ) {

  }

  public static function validFor(ValidationRule $rule) {
    return new static(true, $rule);
  }

  public static function invalidFor(ValidationRule $rule) {
    return new static(false, $rule);
  }

}
