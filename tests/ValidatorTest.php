<?php declare(strict_types=1);

namespace OpenCore\Tests;

use OpenCore\Tests\Models\GuestApplicationForm;
use OpenCore\Validator\Rules\All;
use OpenCore\Validator\Rules\Between;
use OpenCore\Validator\Rules\CountBetween;
use OpenCore\Validator\Rules\CustomRegex;
use OpenCore\Validator\Rules\Each;
use OpenCore\Validator\Rules\Email;
use OpenCore\Validator\Rules\Key;
use OpenCore\Validator\Rules\LenBetween;
use OpenCore\Validator\Rules\Optional;
use OpenCore\Validator\Rules\Required;
use OpenCore\Validator\ValidationResult;
use OpenCore\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery;

final class ValidatorTest extends TestCase {

  use MockeryPHPUnitIntegration;

  protected function tearDown(): void {
    Mockery::close();
  }

  public function testExplicitSimpleRules() {

    $rule = new Required([
      new LenBetween(13, 13),
      new CustomRegex('twoWords', '/^\\w+\\W*\\w+\\W*$/'),
    ]);

    $res = $rule->evaluate('Hello, World!');

    $this->assertEquals([
      'required' => [true, [
        'lenBetween' => true,
        'twoWords' => true,
      ]]
    ], $this->resultToArray($res));

  }

  public function testCreateRulesByModel() {

    $userInput = [
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

    Validator::validateByModel(GuestApplicationForm::class, $userInput); // should not throw

    $res = Validator::createRuleByModel(GuestApplicationForm::class)->evaluate($userInput);

    $this->assertEquals([
      "all" => [true, [
        "key[acceptTerms]" => [true, ["optional" => true]],
        "key[userLogin]" => [true, ["required" => [true, [
          "key[login]" => [true, ["required" => [true, ["lenBetween" => true]]]],
          "key[password]" => [true, ["required" => [true, ["lenBetween" => true]]]],
        ]]]],
        "key[userExtraLogin]" => [true, ["optional" => true]],
        "key[email]" => [true, ["required" => [true, ["email" => true, "lenBetween" => true]]]],
        "key[interests]" => [true, ["optional" => [true, [
          "countBetween" => true,
          "each" => [true, [
            "key[0]" => [true, ["lenBetween" => true]],
            "key[1]" => [true, ["lenBetween" => true]],
          ]],
        ]]]],
        "key[phone]" => [true, ["optional" => [true, ["lenBetween" => true]]]],
        "key[survey]" => [true, ["required" => [true, [
          "key[q0]" => [true, ["optional" => true]],
          "key[q1]" => [true, ["optional" => [true, ["lenBetween" => true]]]],
          "key[q2]" => [true, ["lenBetween" => true]],
          "key[q3]" => [true, ["between" => true]]
        ]]]]
      ]]
    ], $this->resultToArray($res));
  }

  public function testExplicitNestedRulesPass() {
    $rule = new All([
      new Key('login', [new Required([new LenBetween(3, 16)])]),
      new Key('password', [new Required([new LenBetween(8, 16)])]),
      new Key('acceptTerms', [new Required()]),
      new Key('email', [new Required([new LenBetween(5, 24), new Email()])]),
      new Key('phone', [new Optional([new LenBetween(5, 24)])]),
      new Key('interests', [
        new CountBetween(1, 5),
        new Each([new LenBetween(3, 16)]),
      ]),
      new Key('survey', [
        new Key('q0', [new Optional()]),
        new Key('q1', [new Required()]),
        new Key('q2', [new LenBetween(1, 32)]),
        new Key('q3', [new Between(0, 100)]),
      ]),
    ]);

    $res = $rule->evaluate([
      'login' => 'mylogin',
      'password' => '$mypass657$',
      'acceptTerms' => true,
      'email' => 'user@example.com',
      'phone' => '+000 000-000-000',
      'interests' => ['music', 'movie'],
      'survey' => ['q1' => true, 'q2' => 'I dont know', 'q3' => 42],
    ]);

    // echo json_encode($this->resultToArray($res), flags: JSON_PRETTY_PRINT);
    // die;

    $this->assertEquals([
      "all" => [true, [
        "key[acceptTerms]" => [true, ["required" => true]],
        "key[email]" => [true, ["required" => [true, ["email" => true, "lenBetween" => true]]]],
        "key[interests]" => [true, [
          "countBetween" => true,
          "each" => [true, [
            "key[0]" => [true, ["lenBetween" => true]],
            "key[1]" => [true, ["lenBetween" => true]],
          ]],
        ]],
        "key[login]" => [true, ["required" => [true, ["lenBetween" => true]]]],
        "key[password]" => [true, ["required" => [true, ["lenBetween" => true]]]],
        "key[phone]" => [true, ["optional" => [true, ["lenBetween" => true]]]],
        "key[survey]" => [true, [
          "key[q0]" => [true, ["optional" => true]],
          "key[q1]" => [true, ["required" => true]],
          "key[q2]" => [true, ["lenBetween" => true]],
          "key[q3]" => [true, ["between" => true]]
        ]]
      ]]
    ], $this->resultToArray($res));

  }

  public function testExplicitNestedRulesFail() {
    $rule = new All([
      new Key('login', [new Required([new LenBetween(0, 3)])]),
      new Key('password', [new Required([new LenBetween(16, 32)])]),
      new Key('acceptTerms', [new Required()]),
      new Key('email', [new Required([new LenBetween(0, 3), new Email()])]),
      new Key('phone', [new Optional([new LenBetween(5, 8)])]),
      new Key('interests', [
        new CountBetween(0, 1),
        new Each([new LenBetween(2, 3)]),
      ]),
      new Key('survey', [
        new Key('q0', [new Required()]),
        new Key('q1', [new Required()]),
        new Key('q2', [new LenBetween(16, 32)]),
        new Key('q3', [new Between(100, 100)]),
      ]),
    ]);

    $res = $rule->evaluate([
      'login' => 'mylogin',
      'password' => null,
      'acceptTerms' => null,
      'email' => '@user@example.com',
      'phone' => '+000 000-000-000',
      'interests' => ['music', 'movie'],
      'survey' => ['q1' => null, 'q2' => 'I dont know', 'q3' => 42],
    ]);

    $this->assertEquals([
      "all" => [false, [
        "key[acceptTerms]" => [false, ["required" => false]],
        "key[email]" => [false, ["required" => [false, ["email" => false, "lenBetween" => false]]]],
        "key[interests]" => [false, [
          "countBetween" => false,
          "each" => [false, [
            "key[0]" => [false, ["lenBetween" => false]],
            "key[1]" => [false, ["lenBetween" => false]],
          ]],
        ]],
        "key[login]" => [false, ["required" => [false, ["lenBetween" => false]]]],
        "key[password]" => [false, ["required" => false]],
        "key[phone]" => [false, ["optional" => [false, ["lenBetween" => false]]]],
        "key[survey]" => [false, [
          "key[q0]" => [false, ["required" => false]],
          "key[q1]" => [false, ["required" => false]],
          "key[q2]" => [false, ["lenBetween" => false]],
          "key[q3]" => [false, ["between" => false]]
        ]]
      ]]
    ], $this->resultToArray($res));

  }

  private function resultToArray(ValidationResult $result): array {
    if ($result->children) {
      $children = [];
      foreach ($result->children as $child) {
        $children += $this->resultToArray($child);
      }
      ksort($children);
      $val = [$result->valid, $children];
    } else {
      $val = $result->valid;
    }
    $name = $result->rule->getName();
    $index = $result->rule->getIndex();
    if ($index !== null) {
      $name .= "[$index]";
    }
    return [$name => $val];
  }

}
