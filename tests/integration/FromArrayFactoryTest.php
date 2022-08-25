<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil\Tests;

use PHPUnit\Framework\TestCase;
use Xthiago\ValueObject\BancosBrasil\FromArrayFactory;
use Xthiago\ValueObject\BancosBrasil\BancoNaoEncontrado;
use Xthiago\ValueObject\BancosBrasil\NumeroCodigoInvalido;

class FromArrayFactoryTest extends TestCase
{
    private const CODE_BANCO_NACIONAL = '998877';
    private const CODE_BANCO_BAMERINDUS = '665544';
    private const CODE_BANCO_INTERIOR = '332211';

    /** @return array<array-key, array{name: string}> $banksIndexedByCode $banksIndexedByCode */
    private function fixtures(): array
    {
        return [
            self::CODE_BANCO_NACIONAL => ['name' => 'Banco Nacional S.A.'],
            self::CODE_BANCO_BAMERINDUS => ['name' => 'Banco Mercantil e Industrial do Paraná S/A'],
            self::CODE_BANCO_INTERIOR => ['name' => 'Banco Interior de Sao Paulo SA'],
        ];
    }

    public function test_fromString_should_return_the_value_object_when_it_exists_on_factory_settings(): void
    {
        $fixtures = $this->fixtures();
        self::assertCount(3, $fixtures);

        $factory = new FromArrayFactory($fixtures);
        foreach ($fixtures as $codigo => $data) {
            $banco = $factory->fromString((string) $codigo);

            self::assertSame((string) $codigo, $banco->codigo);
            self::assertSame($data['name'], $banco->nome);
        }
    }

    public function test_fromString_should_throw_InvalidBankCode_when_the_provided_code_is_empty(): void
    {
        self::expectExceptionObject(NumeroCodigoInvalido::paraStringVazia());

        $factory = new FromArrayFactory($this->fixtures());
        $emptyCode = '';

        $factory->fromString($emptyCode);
    }

    public function test_fromString_should_throw_BankNotFound_when_the_provided_code_not_exists_on_settings(): void
    {
        $nonExistentCode = '101010';
        $factory = new FromArrayFactory($this->fixtures());

        self::expectExceptionObject(BancoNaoEncontrado::comCodigoCompe($nonExistentCode));

        $factory->fromString($nonExistentCode);
    }

    public function test_withDefaultConfiguration_should_create_factory_with_some_banks(): void
    {
        $factory = FromArrayFactory::withDefaultConfiguration();

        $bancoDoBrasil = $factory->fromString('001');
        $bradesco = $factory->fromString('237');
        $itau = $factory->fromString('341');

        self::assertSame('Banco do Brasil S.A.', $bancoDoBrasil->nome);
        self::assertSame('Bradesco S.A.', $bradesco->nome);
        self::assertSame('Banco Itaú S.A.', $itau->nome);
    }
}
