<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

final class FromArrayFactory implements InstituicaoFinanceiraFactory
{
    /**
     * @param array<array-key, array{name: string}> $banksIndexedByCode $banksIndexedByCode onde array-key é o
     * código da instituição.
     */
    public function __construct(
        private array $banksIndexedByCode,
    ) {
    }

    /** {@inheritDoc} */
    public function fromString(string $bankCode): InstituicaoFinanceira
    {
        if (empty($bankCode)) {
            throw NumeroCodigoInvalido::paraStringVazia();
        }

        if (! isset($this->banksIndexedByCode[$bankCode])) {
            throw BancoNaoEncontrado::comCodigoCompe($bankCode);
        }

        return InstituicaoFinanceira::fromFactory(
            code: $bankCode,
            name: $this->banksIndexedByCode[$bankCode]['name'],
        );
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
}
