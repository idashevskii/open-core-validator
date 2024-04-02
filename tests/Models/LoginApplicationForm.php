<?php declare(strict_types=1);

namespace OpenCore\Tests\Models;

use OpenCore\Validator\Rules\LenBetween;

class LoginApplicationForm {

  #[LenBetween(3, 16)]
  public string $login;

  #[LenBetween(8, 16)]
  public string $password;
}
