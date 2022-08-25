<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use InvalidArgumentException;

class NumeroCodigoInvalido extends InvalidArgumentException
{
    private const INSTRUCOES = 'É esperado uma string com 3 números inteiros positivos.';

    private function __construct(
        string $mensagem,
        public readonly string $codigoCompe,
    ) {
        parent::__construct($mensagem);
    }

    public static function paraStringVazia(): self
    {
        return new self(
            mensagem: 'O código do banco (Número-Código) informado não pode ser uma string vazia. ' . self::INSTRUCOES,
            codigoCompe: '',
        );
    }

    public static function paraStringNoFormatoInvalido(string $codigoCompe): self
    {
        return new self(
            mensagem: 'O código do banco (Número-Código) informado possui formato inválido. ' . self::INSTRUCOES,
            codigoCompe: $codigoCompe,
        );
    }
}
