<?php
declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use InvalidArgumentException;

class ArrayFactory implements BancoFactory
{
    /**
     * @param array<string, array{name: string}> $banksIndexedByCode $banksIndexedByCode
     */
    public function __construct(
        private array $banksIndexedByCode,
    ) {
    }

    public static function withDefaultConfiguration(): static
    {
        return new static(banksIndexedByCode: [
            // TODO: São só exemplos. Depois vou incluir um arquivo estático (e seu gerador) com todas opções.
            '001' => ['name' => 'Banco do Brasil S.A.'],
            '033' => ['name' => 'Banco Santander S.A'],
            '104' => ['name' => 'Caixa Econômica Federal'],
            '341' => ['name' => 'Banco Itaú S.A.'],
            '237' => ['name' => 'Bradesco S.A.'],
        ]);
    }

    /**
     * @throws InvalidArgumentException se não existir banco com código informado.
     */
    public function fromString(string $bankCode): Banco
    {
        if (! isset($this->banksIndexedByCode[$bankCode])) {
            throw new InvalidArgumentException();
        }

        return Banco::fromFactory(
            code: $bankCode,
            name: $this->banksIndexedByCode[$bankCode]['name'],
        );
    }
}
