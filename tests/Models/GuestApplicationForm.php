<?php declare(strict_types=1);

namespace OpenCore\Tests\Models;

use OpenCore\Validator\Rules\Between;
use OpenCore\Validator\Rules\CountBetween;
use OpenCore\Validator\Rules\Each;
use OpenCore\Validator\Rules\Email;
use OpenCore\Validator\Rules\Key;
use OpenCore\Validator\Rules\LenBetween;
use OpenCore\Validator\Rules\Optional;


class GuestApplicationForm {

  public LoginApplicationForm $userLogin;
  public ?LoginApplicationForm $userExtraLogin;

  public bool $acceptTerms = false;

  #[Email]
  #[LenBetween(5, 24)]
  public string $email;

  #[LenBetween(5, 24)]
  public ?string $phone;

  #[CountBetween(1, 5)]
  #[Each([new LenBetween(3, 16)])]
  public array $interests = [];

  #[Key('q0', [new Optional([new LenBetween(0, 16)])])]
  #[Key('q1', [new Optional([new LenBetween(0, 16)])])]
  #[Key('q2', [new LenBetween(1, 32)])]
  #[Key('q3', [new Between(0, 100)])]
  public array $survey;
}
