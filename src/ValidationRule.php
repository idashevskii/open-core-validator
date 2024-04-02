<?php declare(strict_types=1);

namespace OpenCore\Validator;

abstract class ValidationRule {

  protected static ?string $name = null;


  /**
   * @param ValidationRule[] $children
   */
  public function __construct(
    public readonly ?array $options = null,
    public readonly ?array $children = null,
    private readonly ?string $instanceName = null,
  ) {

  }

  public function getIndex(): string|int|null {
    return null;
  }

  public function getName(): string {
    return $this->instanceName ?? static::$name;
  }

  abstract function evaluate(mixed $data): ValidationResult;
}
