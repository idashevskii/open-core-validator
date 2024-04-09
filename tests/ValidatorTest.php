<?php declare(strict_types=1);

namespace OpenCore\Tests;

use OpenCore\Tests\Models\GuestApplicationForm;
use OpenCore\Tests\Models\LoginApplicationForm;
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
use OpenCore\Validator\Type;
use OpenCore\Validator\Arr;
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

  public function testDeserialize() {

    $userInput = [
      'userLogin' => [
        'login' => 'mylogin',
        'password' => '$mypass657$',
      ],
      'userExtraLogins' => [
        [
          'login' => 'extraLogin1<p>\0',
          'password' => '<script>\0',
        ]
      ],
      'nestedArrs' => [[['a1'], ['a2']], [['a3'], ['a4']]],
      'acceptTerms' => true,
      'email' => 'user@example.com',
      'interests' => ['music', 'movie'],
      'survey' => ['q1' => 'yes', 'q2' => 'I dont know', 'q3' => '-42'],
    ];

    $actual = Validator::deserialize(GuestApplicationForm::class, $userInput);

    $expected = new GuestApplicationForm();
    $expected->userLogin = LoginApplicationForm::create('mylogin', '$mypass657$');
    $expected->email = 'user@example.com';
    $expected->interests = ['music', 'movie'];
    $expected->nestedArrs = [[['a1'], ['a2']], [['a3'], ['a4']]];
    $expected->phone = '+000 123-456-789';
    $expected->survey = ['q1' => 'yes', 'q2' => 'I dont know', 'q3' => -42];
    $expected->userExtraLogins = [LoginApplicationForm::create('extraLogin1<p>\0', '<script>\0')];
    $expected->acceptTerms = true;

    $this->assertEquals($expected, $actual);
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

  public function testNdArrayModel() {

    $userInput = [
      [['000', '001'],
        ['010', '011']],
      [['100', '101'],
        ['110', '111']],
    ];

    $rules = Validator::createRuleByModel(Arr::classOf(
      Arr::classOf(Arr::classOf(Type::STRING)))); // should not throw

    $res = $rules->evaluate($userInput);

    $this->assertEquals([
      "each" => [true, [
        "key[0]" => [true, [
          "each" => [true, [
            "key[0]" => [true, [
              "each" => [true, [
                "key[0]" => [true, ['string' => true]],
                "key[1]" => [true, ['string' => true]],
              ]],
            ]],
            "key[1]" => [true, [
              "each" => [true, [
                "key[0]" => [true, ['string' => true]],
                "key[1]" => [true, ['string' => true]],
              ]],
            ]],
          ]],
        ]],
        "key[1]" => [true, [
          "each" => [true, [
            "key[0]" => [true, [
              "each" => [true, [
                "key[0]" => [true, ['string' => true]],
                "key[1]" => [true, ['string' => true]],
              ]],
            ]],
            "key[1]" => [true, [
              "each" => [true, [
                "key[0]" => [true, ['string' => true]],
                "key[1]" => [true, ['string' => true]],
              ]],
            ]],
          ]],
        ]],
      ]],
    ], $this->resultToArray($res));
  }

  public function testNdArrayModelFail() {

    $userInput = [
      [[false, 001],
        [new \stdClass, []]],
    ];

    $rules = Validator::createRuleByModel(Arr::classOf(
      Arr::classOf(Arr::classOf(Type::STRING)))); // should not throw

    $res = $rules->evaluate($userInput);

    $this->assertEquals([
      "each" => [false, [
        "key[0]" => [false, [
          "each" => [false, [
            "key[0]" => [false, [
              "each" => [false, [
                "key[0]" => [false, ['string' => false]],
                "key[1]" => [false, ['string' => false]],
              ]],
            ]],
            "key[1]" => [false, [
              "each" => [false, [
                "key[0]" => [false, ['string' => false]],
                "key[1]" => [false, ['string' => false]],
              ]],
            ]],
          ]],
        ]],
      ]],
    ], $this->resultToArray($res));
  }

  public function testCreateRulesByModel() {

    $userInput = [
      'userLogin' => [
        'login' => 'mylogin',
        'password' => '$mypass657$',
      ],
      'userExtraLogins' => [
        [
          'login' => 'extraLogin1<p>\0',
          'password' => '<script>\0',
        ]
      ],
      'acceptTerms' => true,
      'email' => 'user@example.com',
      'phone' => '+000 000-000-000',
      'interests' => ['music', 'movie'],
      'survey' => ['q1' => 'yes', 'q2' => 'I dont know', 'q3' => '42'],
    ];

    Validator::validateByModel(GuestApplicationForm::class, $userInput); // should not throw

    $res = Validator::createRuleByModel(GuestApplicationForm::class)->evaluate($userInput);

    $this->assertEquals([
      "all" => [true, [
        "key[acceptTerms]" => [true, ["optional" => [true, ["bool" => true]]]],
        "key[userLogin]" => [true, ["required" => [true, [
          "key[login]" => [true, ["required" => [true, ["string" => true, "lenBetween" => true]]]],
          "key[password]" => [true, ["required" => [true, ["string" => true, "lenBetween" => true]]]],
        ]]]],
        "key[userExtraLogins]" => [true, ["optional" => [true, [
          "each" => [true, [
            "key[0]" => [true, [
              "key[login]" => [true, ["required" => [true, ["string" => true, "lenBetween" => true]]]],
              "key[password]" => [true, ["required" => [true, ["string" => true, "lenBetween" => true]]]],
            ]],
          ]],
        ]]]],
        "key[nestedArrs]" => [true, ["optional" => true]],
        "key[email]" => [true, ["required" => [true, ["string" => true, "email" => true, "lenBetween" => true]]]],
        "key[interests]" => [true, ["optional" => [true, [
          "countBetween" => true,
          "each" => [true, [
            "key[0]" => [true, ["string" => true]],
            "key[1]" => [true, ["string" => true]],
          ]],
          "each#2" => [true, [
            "key[0]" => [true, ["lenBetween" => true]],
            "key[1]" => [true, ["lenBetween" => true]],
          ]],
        ]]]],
        "key[phone]" => [true, ["optional" => [true, ["string" => true, "lenBetween" => true]]]],
        "key[survey]" => [true, ["required" => [true, [
          "each" => [true, [
            "key[q1]" => [true, ["mixed" => true]],
            "key[q2]" => [true, ["mixed" => true]],
            "key[q3]" => [true, ["mixed" => true]],
          ]],
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
      'survey' => ['q1' => true, 'q2' => 'I dont know', 'q3' => '42'],
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
        $repeatedKeys = [];
        foreach ($this->resultToArray($child) as $k => $v) {
          if (isset($children[$k])) {
            if (!isset($repeatedKeys[$k])) {
              $repeatedKeys[$k] = 1;
            }
            $k .= '#' . (++$repeatedKeys[$k]);
          }
          $children[$k] = $v;
        }
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
