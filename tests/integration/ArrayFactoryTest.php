<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil\Tests;

use PHPUnit\Framework\TestCase;
use Xthiago\ValueObject\BancosBrasil\ArrayFactory;
use Xthiago\ValueObject\BancosBrasil\BankNotFound;
use Xthiago\ValueObject\BancosBrasil\InvalidBankCode;

class ArrayFactoryTest extends TestCase
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

        $factory = new ArrayFactory($fixtures);
        foreach ($fixtures as $codigo => $data) {
            $banco = $factory->fromString((string) $codigo);

            self::assertSame((string) $codigo, $banco->code);
            self::assertSame($data['name'], $banco->name);
        }
    }

    public function test_fromString_should_throw_InvalidBankCode_when_the_provided_code_is_empty(): void
    {
        self::expectExceptionObject(InvalidBankCode::forEmptyCode());

        $factory = new ArrayFactory($this->fixtures());
        $emptyCode = '';

        $factory->fromString($emptyCode);
    }

    public function test_fromString_should_throw_BankNotFound_when_the_provided_code_not_exists_on_settings(): void
    {
        $nonExistentCode = '101010';
        $factory = new ArrayFactory($this->fixtures());

        self::expectExceptionObject(BankNotFound::forCode($nonExistentCode));

        $factory->fromString($nonExistentCode);
    }

    public function test_withDefaultConfiguration_should_create_factory_with_some_banks(): void
    {
        $factory = ArrayFactory::withDefaultConfiguration();

        $bancoDoBrasil = $factory->fromString('001');
        $bradesco = $factory->fromString('237');
        $itau = $factory->fromString('341');

        self::assertSame('Banco do Brasil S.A.', $bancoDoBrasil->name);
        self::assertSame('Bradesco S.A.', $bradesco->name);
        self::assertSame('Banco Itaú S.A.', $itau->name);
    }
}
