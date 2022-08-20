<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

/**
 * Fábrica responsável por criar as instâncias do objeto de valor Banco a partir da string fornecida (código).
 *
 * Por padrão a biblioteca cria baseado em uma lista de bancos estática. Você pode criar sua própria Factory consultar
 * os dados em um sistema de banco de dados ou micro-serviço.
 */
interface BancoFactory
{
    /**
     * @param non-empty-string $bankCode
     *
     * @throws InvalidBankCode se o código possuir um formato inválido (ex: string vazia).
     * @throws BankNotFound se não existir banco com código informado.
     */
    public function fromString(string $bankCode): Banco;
}
