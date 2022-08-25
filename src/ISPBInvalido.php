<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use InvalidArgumentException;

class ISPBInvalido extends InvalidArgumentException
{
    private const INSTRUCOES = 'É esperado uma string com 8 números inteiros positivos.';

    private function __construct(
        string $mensagem,
        public readonly string $ispb,
    ) {
        parent::__construct($mensagem);
    }

    public static function paraStringVazia(): self
    {
        return new self(
            mensagem: 'O código do banco (ISPB) informado não pode ser uma string vazia. ' . self::INSTRUCOES,
            ispb: '',
        );
    }

    public static function paraStringNoFormatoInvalido(string $ispb): self
    {
        return new self(
            mensagem: 'O código do banco (ISPB) informado possui formato inválido. ' . self::INSTRUCOES,
            ispb: $ispb,
        );
    }
}
