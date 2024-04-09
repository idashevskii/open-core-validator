## OpenCore Validator

*Simple, fast and lightweight validator based on attributes and internal DSL*

Key features:
- Hi-level API using PHP Attributes and low-level API using internal DSL
- Support for nesting typed structures
- Support for nested and typed arrays
- Deserialization

### Example

Having some complex models:

```php
class LoginApplicationForm {

  #[LenBetween(3, 16)]
  public string $login;

  #[LenBetween(8, 16)]
  public string $password;
}

class GuestApplicationForm {

  public LoginApplicationForm $userLogin;

  public bool $acceptTerms = false;

  #[Email]
  #[LenBetween(5, 24)]
  public ?string $email;

  #[CountBetween(1, 5)]
  #[Each([new LenBetween(3, 16)])]
  #[Arr(Type::STRING)]
  public array $interests = [];

  #[Key('q1', [new Optional([new LenBetween(0, 16)])])]
  #[Key('q2', [new LenBetween(1, 32)])]
  #[Key('q3', [new Between(0, 100)])]
  #[Arr(Type::MIXED)]
  public array $survey;
}
```

And user input:
```php
$userInput=[
  'userLogin' => [
    'login' => 'mylogin',
    'password' => '$mypass657$',
  ],
  'acceptTerms' => true,
  'email' => 'user@example.com',
  'phone' => '+000 000-000-000',
  'interests' => ['music', 'movie'],
  'survey' => ['q1' => 'yes', 'q2' => 'I dont know', 'q3' => 42],
];
```

Validate data according to model:
```php
try{
  Validator::validateByModel(GuestApplicationForm::class, $userInput);
}catch(ValidationException $ex){
  // inspect $ex->result
}
```

Or even deserialize data to object after validation:
```php
try{
  $form = Validator::deserialize(GuestApplicationForm::class, $userInput);
}catch(ValidationException $ex){
  // inspect $ex->result
}
```
