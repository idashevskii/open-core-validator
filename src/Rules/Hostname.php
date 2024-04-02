<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Hostname extends AbstractStaticRegex {

  protected static string $regex = '#^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:[a-z0-9][a-z0-9-]{0,61})?[a-z0-9]$#';
  protected static ?string $name = 'hostname';

}
