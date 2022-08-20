<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

final class ArrayFactory implements BancoFactory
{
    /**
     * @param array<array-key, array{name: string}> $banksIndexedByCode $banksIndexedByCode
     */
    public function __construct(
        private array $banksIndexedByCode,
    ) {
    }

    public static function withDefaultConfiguration(): self
    {
        return new self(banksIndexedByCode: [
            // TODO: São só exemplos. Depois vou incluir um arquivo estático (e seu gerador) com todas opções.
            '001' => ['name' => 'Banco do Brasil S.A.'],
            '033' => ['name' => 'Banco Santander S.A'],
            '104' => ['name' => 'Caixa Econômica Federal'],
            '341' => ['name' => 'Banco Itaú S.A.'],
            '237' => ['name' => 'Bradesco S.A.'],
        ]);
    }

    /** {@inheritDoc} */
    public function fromString(string $bankCode): Banco
    {
        if (empty($bankCode)) {
            throw InvalidBankCode::forEmptyCode();
        }

        if (! isset($this->banksIndexedByCode[$bankCode])) {
            throw BankNotFound::forCode($bankCode);
        }

        return Banco::fromFactory(
            code: $bankCode,
            name: $this->banksIndexedByCode[$bankCode]['name'],
        );
    }
}
