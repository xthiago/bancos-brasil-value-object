<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use JsonSerializable;
use Stringable;

use function strlen;

/**
 * Representa o ISPB da instituição no Sistema de Pagamentos Brasileiro (SPB).
 *
 * Da documentação do Banco Central:
 *
 * > ISPB: Identificador do participante junto ao Banco Central para o Sistema de Pagamentos Brasileiro – código
 * > identificador atribuído a todos os participantes do Sistema de Transferência de Reservas (STR)
 * >
 * > Fonte: https://www.bcb.gov.br/pom/spb/estatistica/port/ASTR003.pdf - acessado em 2022-08-21.
 */
class ISPB implements Stringable, JsonSerializable
{
    private function __construct(
        public readonly string $codigo,
    ) {
        if (strlen($codigo) === 0) {
            throw ISPBInvalido::paraStringVazia();
        }

//        if (! preg_match(self::PATTERN_TO_MATCH_ISPB, $codigo, $matches)) {
//            throw ISPBInvalido::paraStringNoFormatoInvalido($codigo);
//        }
    }

    /**
     * @param non-empty-string $compeCode
     *
     * @throws ISPBInvalido
     */
    public static function fromString(string $compeCode): self
    {
        return new self($compeCode);
    }

    /** @return array{code: string} */
    public function jsonSerialize(): array
    {
        return [
            'codigo' => $this->codigo,
        ];
    }

    public function __toString(): string
    {
        return $this->codigo;
    }

    public function isEqualTo(self $other): bool
    {
        return $this->codigo === $other->codigo;
    }

    public function equals(?object $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->codigo === $other->codigo;
    }
}
