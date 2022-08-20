<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;
use Xthiago\ValueObject\BancosBrasil\ArrayFactory;
use Xthiago\ValueObject\BancosBrasil\Banco;
use Xthiago\ValueObject\BancosBrasil\BancoFactory;
use Xthiago\ValueObject\BancosBrasil\BankNotFound;
use Xthiago\ValueObject\BancosBrasil\InvalidBankCode;

use function is_string;
use function json_encode;
use function sprintf;

class BancoTest extends TestCase
{
    private const CODE_BANCO_NACIONAL = '998877';
    private const CODE_BANCO_BAMERINDUS = '665544';
    private const CODE_BANCO_INTERIOR = '332211';

    private ?BancoFactory $factory;

    /** @return array<array-key, array{name: string}> $banksIndexedByCode $banksIndexedByCode */
    private function fixtures(): array
    {
        return [
            self::CODE_BANCO_NACIONAL => ['name' => 'Banco Nacional S.A.'],
            self::CODE_BANCO_BAMERINDUS => ['name' => 'Banco Mercantil e Industrial do Paraná S/A'],
            self::CODE_BANCO_INTERIOR => ['name' => 'Banco Interior de Sao Paulo SA'],
        ];
    }

    protected function setUp(): void
    {
        $this->factory = new ArrayFactory($this->fixtures());
    }

    protected function tearDown(): void
    {
        Banco::resetFactory();
    }

    public function test_should_create_instances_using_default_factory(): void
    {
        $codeBancoBrasil = '001';

        $resultOfFromString = Banco::fromString($codeBancoBrasil);
        $resultOfTryFromString = Banco::tryFromString($codeBancoBrasil);

        self::assertTrue(
            $resultOfFromString->isEqualTo(Banco::fromFactory(code: '001', name: 'Banco do Brasil S.A.'))
        );
        self::assertSame('001', $resultOfFromString->code);
        self::assertSame('Banco do Brasil S.A.', $resultOfFromString->name);

        self::assertTrue(
            $resultOfTryFromString->isEqualTo(Banco::fromFactory(code: '001', name: 'Banco do Brasil S.A.'))
        );
        self::assertSame('001', $resultOfTryFromString->code);
        self::assertSame('Banco do Brasil S.A.', $resultOfTryFromString->name);
    }

    public function test_should_create_instances_using_another_factory(): void
    {
        $bankCode = self::CODE_BANCO_BAMERINDUS;
        try {
            Banco::fromString($bankCode);
            self::fail(sprintf('Default factory should not have an entry for bank code "%s".', $bankCode));
        } catch (Throwable $exception) {
            self::assertEquals(BankNotFound::forCode($bankCode), $exception);
        }

        self::assertNull(Banco::tryFromString($bankCode));

        Banco::setFactory($this->factory);
        $resultFromString = Banco::fromString($bankCode);
        $resultOfTryFromString = Banco::tryFromString($bankCode);

        self::assertTrue(
            $resultFromString->isEqualTo(
                Banco::fromFactory(code: $bankCode, name: 'Banco Mercantil e Industrial do Paraná S/A')
            )
        );
        self::assertSame($bankCode, $resultFromString->code);
        self::assertSame('Banco Mercantil e Industrial do Paraná S/A', $resultFromString->name);

        self::assertTrue(
            $resultOfTryFromString->isEqualTo(
                Banco::fromFactory(code: $bankCode, name: 'Banco Mercantil e Industrial do Paraná S/A')
            )
        );
        self::assertSame($bankCode, $resultOfTryFromString->code);
        self::assertSame('Banco Mercantil e Industrial do Paraná S/A', $resultOfTryFromString->name);
    }

    public function test_fromString_should_throw_BankNotFound_when_bank_is_not_found(): void
    {
        self::expectExceptionObject(BankNotFound::forCode('000'));

        $inlineFactory = new class () implements BancoFactory {
            public function fromString(string $bankCode): Banco
            {
                throw BankNotFound::forCode('000');
            }
        };
        Banco::setFactory($inlineFactory);
        Banco::fromString('000');
    }

    public function test_tryFromString_should_return_null_when_bank_is_not_found(): void
    {
        $inlineFactory = new class () implements BancoFactory {
            public function fromString(string $bankCode): Banco
            {
                throw BankNotFound::forCode('000');
            }
        };
        Banco::setFactory($inlineFactory);

        self::assertNull(Banco::tryFromString('000'));
    }

    public function test_fromString_should_not_catch_exception_thrown_by_factory(): void
    {
        self::expectExceptionObject(new Exception('Catch me if you can!'));

        $inlineFactory = new class () implements BancoFactory {
            public function fromString(string $bankCode): Banco
            {
                throw new Exception('Catch me if you can!');
            }
        };
        Banco::setFactory($inlineFactory);
        Banco::fromString('000');
    }

    public function test_tryFromString_should_not_catch_non_BankNotFound_exceptions_thrown_by_factory(): void
    {
        self::expectExceptionObject(new Exception('Catch me if you can!'));

        $inlineFactory = new class () implements BancoFactory {
            public function fromString(string $bankCode): Banco
            {
                throw new Exception('Catch me if you can!');
            }
        };
        Banco::setFactory($inlineFactory);
        Banco::tryFromString('000');
    }

    public function test_fromFactory_should_throw_InvalidBankCode_when_empty_code_is_given(): void
    {
        self::expectExceptionObject(InvalidBankCode::forEmptyCode());
        $emptyCode = '';

        Banco::fromFactory(
            code: $emptyCode,
            name: 'Xablau'
        );
    }

    public function test_it_should_be_serializable(): void
    {
        Banco::setFactory($this->factory);

        $bank = Banco::fromString(self::CODE_BANCO_INTERIOR);

        // Stringable interface:
        self::assertSame(self::CODE_BANCO_INTERIOR, $bank->__toString());
        self::assertSame(self::CODE_BANCO_INTERIOR, (string) $bank);

        // JsonSerializable interface:
        $resultOfJsonSerializeMethod = $bank->jsonSerialize();
        $resultOfJsonEncodeFunction = json_encode($bank);

        $expectedArray = [
            'code' => self::CODE_BANCO_INTERIOR,
            'name' => 'Banco Interior de Sao Paulo SA',
        ];
        $expectedJson = <<<JSON
{
    "code":  "332211",
    "name":  "Banco Interior de Sao Paulo SA"
}
JSON;

        self::assertSame($expectedArray, $resultOfJsonSerializeMethod);
        self::assertJsonStringEqualsJsonString($expectedJson, $resultOfJsonEncodeFunction);
    }

    /** @dataProvider isEqualToProvider */
    public function test_isEqualTo(string $firstCode, string $secondCode, bool $isEqual): void
    {
        Banco::setFactory($this->factory);
        $firstBank = Banco::fromString($firstCode);
        $secondBank = Banco::fromString($secondCode);

        self::assertSame($isEqual, $firstBank->isEqualTo($secondBank));
    }

    /** @return array{firstCode: string, secondCode: string, isEqual: bool}[] */
    public function isEqualToProvider(): array
    {
        return [
            [
                'firstCode' => self::CODE_BANCO_INTERIOR,
                'secondCode' => self::CODE_BANCO_INTERIOR,
                'isEqual' => true,
            ],
            [
                'firstCode' => self::CODE_BANCO_BAMERINDUS,
                'secondCode' => self::CODE_BANCO_BAMERINDUS,
                'isEqual' => true,
            ],
            [
                'firstCode' => self::CODE_BANCO_NACIONAL,
                'secondCode' => self::CODE_BANCO_NACIONAL,
                'isEqual' => true,
            ],
            [
                'firstCode' => self::CODE_BANCO_INTERIOR,
                'secondCode' => self::CODE_BANCO_BAMERINDUS,
                'isEqual' => false,
            ],
            [
                'firstCode' => self::CODE_BANCO_INTERIOR,
                'secondCode' => self::CODE_BANCO_NACIONAL,
                'isEqual' => false,
            ],
            [
                'firstCode' => self::CODE_BANCO_NACIONAL,
                'secondCode' => self::CODE_BANCO_BAMERINDUS,
                'isEqual' => false,
            ],
        ];
    }

    /** @dataProvider equalsProvider */
    public function test_equals(
        string $bank,
        string|object $other,
        bool $isEqual
    ): void {
        Banco::setFactory($this->factory);

        $aBank = Banco::fromString($bank);
        $otherBank = is_string($other) ? Banco::fromString($other) : $other;

        self::assertSame($isEqual, $aBank->equals($otherBank));
    }

    /** @return array{bank: string, other: string|object, isEqual: bool}[] */
    public function equalsProvider(): array
    {
        return [
            [
                'bank' => self::CODE_BANCO_INTERIOR,
                'other' => self::CODE_BANCO_INTERIOR,
                'isEqual' => true,
            ],
            [
                'bank' => self::CODE_BANCO_NACIONAL,
                'other' => self::CODE_BANCO_NACIONAL,
                'isEqual' => true,
            ],
            [
                'bank' => self::CODE_BANCO_BAMERINDUS,
                'other' => self::CODE_BANCO_BAMERINDUS,
                'isEqual' => true,
            ],

            [
                'bank' => self::CODE_BANCO_INTERIOR,
                'other' => self::CODE_BANCO_NACIONAL,
                'isEqual' => false,
            ],
            [
                'bank' => self::CODE_BANCO_INTERIOR,
                'other' => self::CODE_BANCO_BAMERINDUS,
                'isEqual' => false,
            ],
            [
                'bank' => self::CODE_BANCO_NACIONAL,
                'other' => self::CODE_BANCO_BAMERINDUS,
                'isEqual' => false,
            ],

            [
                'bank' => self::CODE_BANCO_NACIONAL,
                'other' => new stdClass(),
                'isEqual' => false,
            ],
        ];
    }
}
