<?php declare(strict_types=1);

namespace OpenCore\Validator;

use RuntimeException;

class ValidationException extends RuntimeException {

  public function __construct(public readonly ValidationResult $result) {
    parent::__construct();
  }

}
