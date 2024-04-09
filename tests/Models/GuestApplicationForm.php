<?php declare(strict_types=1);

namespace OpenCore\Tests\Models;

use OpenCore\Validator\Rules\ArrayList;
use OpenCore\Validator\Rules\Between;
use OpenCore\Validator\Rules\CountBetween;
use OpenCore\Validator\Rules\Each;
use OpenCore\Validator\Rules\Email;
use OpenCore\Validator\Rules\Key;
use OpenCore\Validator\Rules\LenBetween;
use OpenCore\Validator\Rules\Optional;
use OpenCore\Validator\Type;
use OpenCore\Validator\Arr;


class GuestApplicationForm {

  public LoginApplicationForm $userLogin;

  #[Arr(LoginApplicationForm::class)]
  public ?array $userExtraLogins;

  #[Arr(new Arr(new Arr(Type::STRING)))]
  public ?array $nestedArrs;

  public bool $acceptTerms = false;

  #[Email]
  #[LenBetween(5, 24)]
  public string $email;

  #[LenBetween(5, 24)]
  public string $phone = '+000 123-456-789';

  #[CountBetween(1, 5)]
  #[Each([new LenBetween(3, 16)])]
  #[Arr(Type::STRING)]
  public array $interests = [];

  #[Key('q0', [new Optional([new LenBetween(0, 16)])])]
  #[Key('q1', [new Optional([new LenBetween(0, 16)])])]
  #[Key('q2', [new LenBetween(1, 32)])]
  #[Key('q3', [new Between(-100, 100)])]
  #[Arr(Type::MIXED)]
  public array $survey;
}
