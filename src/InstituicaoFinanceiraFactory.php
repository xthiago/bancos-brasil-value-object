<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

/**
 * Responsável por criar as instâncias do objeto de valor BankCode a partir do código bancário.
 *
 * Por padrão a biblioteca usa uma lista de bancos estática embutida no código fonte. Você pode criar sua própria
 * Factory para consultar os dados onde quiser (sistema de banco de dados ou micro-serviço).
 *
 * @see FromArrayFactory é a implementação padrão.
 */
interface InstituicaoFinanceiraFactory
{
    /**
     * @param non-empty-string $code
     *
     * @throws NumeroCodigoInvalido se o código possuir um formato inválido (ex: string vazia).
     * @throws BancoNaoEncontrado se não existir banco com código informado.
     */
    public function fromString(string $code): InstituicaoFinanceira;
}
