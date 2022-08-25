<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use InvalidArgumentException;

use function sprintf;

class BancoNaoEncontrado extends InvalidArgumentException
{
    private function __construct(
        string $mensagem,
        public readonly string $bankCode,
    ) {
        parent::__construct($mensagem);
    }

    public static function comCodigoCompe(string|NumeroCodigo $codigoCompe): self
    {
        $compe = $codigoCompe instanceof NumeroCodigo ? $codigoCompe->codigo : $codigoCompe;

        return new self(
            mensagem: sprintf(
                'Não existe instituição financeira com o código de compensação (compe) informado ("%s")',
            ),
            bankCode: $compe
        );
    }
}
