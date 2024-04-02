<?php declare(strict_types=1);

namespace OpenCore\Validator\Rules;

abstract class AbstractStaticRegex extends AbstractRegex {

  protected static string $regex = '';

  public function __construct() {
    parent::__construct();
  }

  public function getRegex(): string {
    return static::$regex;
  }
}
