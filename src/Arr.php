<?php declare(strict_types=1);

namespace OpenCore\Validator;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Arr {

  const TYPE = 'array';

  public function __construct(
    public self|string $subtype,
  ) {
  }

  public function toClass() {
    $subtype = $this->subtype;
    if ($subtype instanceof self) {
      return self::classOf($subtype->toClass());
    }
    return self::classOf($subtype);
  }

  public static function classOf(string $type) {
    return self::TYPE . '[' . $type . ']';
  }

  public static function isArr(string $type) {
    return str_starts_with($type, self::TYPE . '[');
  }

  public static function subtypeFromClass(string $type): string {
    return substr($type, 6, -1); // stript array[] wrapper
  }

}
