<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;
use Xthiago\ValueObject\BancosBrasil\FromArrayFactory;
use Xthiago\ValueObject\BancosBrasil\InstituicaoFinanceira;
use Xthiago\ValueObject\BancosBrasil\InstituicaoFinanceiraFactory;
use Xthiago\ValueObject\BancosBrasil\BancoNaoEncontrado;
use Xthiago\ValueObject\BancosBrasil\NumeroCodigoInvalido;

use function is_string;
use function json_encode;
use function sprintf;

class BankCodeTest extends TestCase
{
    private const CODE_BANCO_NACIONAL = '998877';
    private const CODE_BANCO_BAMERINDUS = '665544';
    private const CODE_BANCO_INTERIOR = '332211';

    private ?InstituicaoFinanceiraFactory $factory;

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
        $this->factory = new FromArrayFactory($this->fixtures());
    }

    protected function tearDown(): void
    {
        InstituicaoFinanceira::resetFactory();
    }

    public function test_should_create_instances_using_default_factory(): void
    {
        $codeBancoBrasil = '001';

        $resultOfFromString = InstituicaoFinanceira::fromString($codeBancoBrasil);
        $resultOfTryFromString = InstituicaoFinanceira::tryFromString($codeBancoBrasil);

        self::assertTrue(
            $resultOfFromString->isEqualTo(InstituicaoFinanceira::fromFactory(code: '001', name: 'Banco do Brasil S.A.'))
        );
        self::assertSame('001', $resultOfFromString->codigo);
        self::assertSame('Banco do Brasil S.A.', $resultOfFromString->nome);

        self::assertTrue(
            $resultOfTryFromString->isEqualTo(InstituicaoFinanceira::fromFactory(code: '001', name: 'Banco do Brasil S.A.'))
        );
        self::assertSame('001', $resultOfTryFromString->codigo);
        self::assertSame('Banco do Brasil S.A.', $resultOfTryFromString->nome);
    }

    public function test_should_create_instances_using_another_factory(): void
    {
        $bankCode = self::CODE_BANCO_BAMERINDUS;
        try {
            InstituicaoFinanceira::fromString($bankCode);
            self::fail(sprintf('Default factory should not have an entry for bank code "%s".', $bankCode));
        } catch (Throwable $exception) {
            self::assertEquals(BancoNaoEncontrado::comCodigoCompe($bankCode), $exception);
        }

        self::assertNull(InstituicaoFinanceira::tryFromString($bankCode));

        InstituicaoFinanceira::setFactory($this->factory);
        $resultFromString = InstituicaoFinanceira::fromString($bankCode);
        $resultOfTryFromString = InstituicaoFinanceira::tryFromString($bankCode);

        self::assertTrue(
            $resultFromString->isEqualTo(
                InstituicaoFinanceira::fromFactory(code: $bankCode, name: 'Banco Mercantil e Industrial do Paraná S/A')
            )
        );
        self::assertSame($bankCode, $resultFromString->codigo);
        self::assertSame('Banco Mercantil e Industrial do Paraná S/A', $resultFromString->nome);

        self::assertTrue(
            $resultOfTryFromString->isEqualTo(
                InstituicaoFinanceira::fromFactory(code: $bankCode, name: 'Banco Mercantil e Industrial do Paraná S/A')
            )
        );
        self::assertSame($bankCode, $resultOfTryFromString->codigo);
        self::assertSame('Banco Mercantil e Industrial do Paraná S/A', $resultOfTryFromString->nome);
    }

    public function test_fromString_should_throw_BankNotFound_when_bank_is_not_found(): void
    {
        self::expectExceptionObject(BancoNaoEncontrado::comCodigoCompe('000'));

        $inlineFactory = new class () implements InstituicaoFinanceiraFactory {
            public function fromString(string $bankCode): InstituicaoFinanceira
            {
                throw BancoNaoEncontrado::comCodigoCompe('000');
            }
        };
        InstituicaoFinanceira::setFactory($inlineFactory);
        InstituicaoFinanceira::fromString('000');
    }

    public function test_tryFromString_should_return_null_when_bank_is_not_found(): void
    {
        $inlineFactory = new class () implements InstituicaoFinanceiraFactory {
            public function fromString(string $bankCode): InstituicaoFinanceira
            {
                throw BancoNaoEncontrado::comCodigoCompe('000');
            }
        };
        InstituicaoFinanceira::setFactory($inlineFactory);

        self::assertNull(InstituicaoFinanceira::tryFromString('000'));
    }

    public function test_fromString_should_not_catch_exception_thrown_by_factory(): void
    {
        self::expectExceptionObject(new Exception('Catch me if you can!'));

        $inlineFactory = new class () implements InstituicaoFinanceiraFactory {
            public function fromString(string $bankCode): InstituicaoFinanceira
            {
                throw new Exception('Catch me if you can!');
            }
        };
        InstituicaoFinanceira::setFactory($inlineFactory);
        InstituicaoFinanceira::fromString('000');
    }

    public function test_tryFromString_should_not_catch_non_BankNotFound_exceptions_thrown_by_factory(): void
    {
        self::expectExceptionObject(new Exception('Catch me if you can!'));

        $inlineFactory = new class () implements InstituicaoFinanceiraFactory {
            public function fromString(string $bankCode): InstituicaoFinanceira
            {
                throw new Exception('Catch me if you can!');
            }
        };
        InstituicaoFinanceira::setFactory($inlineFactory);
        InstituicaoFinanceira::tryFromString('000');
    }

    public function test_fromFactory_should_throw_InvalidBankCode_when_empty_code_is_given(): void
    {
        self::expectExceptionObject(NumeroCodigoInvalido::paraStringVazia());
        $emptyCode = '';

        InstituicaoFinanceira::fromFactory(
            code: $emptyCode,
            name: 'Xablau'
        );
    }

    public function test_it_should_be_serializable(): void
    {
        InstituicaoFinanceira::setFactory($this->factory);

        $bank = InstituicaoFinanceira::fromString(self::CODE_BANCO_INTERIOR);

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
        InstituicaoFinanceira::setFactory($this->factory);
        $firstBank = InstituicaoFinanceira::fromString($firstCode);
        $secondBank = InstituicaoFinanceira::fromString($secondCode);

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
        InstituicaoFinanceira::setFactory($this->factory);

        $aBank = InstituicaoFinanceira::fromString($bank);
        $otherBank = is_string($other) ? InstituicaoFinanceira::fromString($other) : $other;

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
