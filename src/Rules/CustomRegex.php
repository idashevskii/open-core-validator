<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CustomRegex extends AbstractRegex {

  public function __construct(string $name, string $regex) {
    parent::__construct(instanceName: $name, options: ['regex' => $regex]);
  }

  public function getRegex(): string {
    return $this->options['regex'];
  }
}
