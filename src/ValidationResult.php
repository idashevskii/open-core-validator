<?php declare(strict_types=1);

namespace OpenCore\Validator;

final class ValidationResult {
  public function __construct(
    public readonly bool $valid,
    public readonly ValidationRule $rule,
    public readonly ?array $children = null,
  ) {}

  public static function validFor(ValidationRule $rule) {
    return new static(true, $rule);
  }

  public static function invalidFor(ValidationRule $rule) {
    return new static(false, $rule);
  }

  public function flatten(): array {
    $ret = [];
    $inspectRec = static function (array $prefix, ValidationResult $vr) use (&$ret, &$inspectRec) {
      if ($vr->children) {
        foreach ($vr->children as $childVr) {
          /** @var ValidationResult $childVr */
          if ($childVr->valid) {
            continue;
          }
          $index = $childVr->rule->getIndex();
          $childPrefix = $index !== null ? array_merge($prefix, [$index]) : $prefix;
          $inspectRec($childPrefix, $childVr);
        }
      } else {
        $ret[implode('/', $prefix)][] = $vr->rule;
      }
    };
    if (!$this->valid) {
      $inspectRec([], $this);
    }
    return $ret;
  }

  public function inspect(): array {
    $ret = [];
    foreach ($this->flatten() as $field => $rules) {
      foreach ($rules as $rule) {
        $details = $rule->getDetails();
        $ret[$field][] = ['rule' => $rule->getName()] + ($details ? ['details' => $details] : []);
      }
    }
    return $ret;
  }
}
