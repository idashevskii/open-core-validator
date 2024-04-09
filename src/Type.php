<?php declare(strict_types=1);

namespace OpenCore\Validator;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Type {

  const STRING = 'string';
  const FLOAT = 'float';
  const INT = 'int';
  const BOOL = 'bool';
  const MIXED = 'mixed';

}
