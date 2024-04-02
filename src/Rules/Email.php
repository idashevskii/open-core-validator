<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email extends AbstractStaticRegex {

  protected static string $regex = '#^[^@]+@[^@]+\.[^@]+$#';
  protected static ?string $name = 'email';

}
