<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use JsonSerializable;
use Stringable;

use function strlen;

/**
 * Representa o Número-Código (antigo Compe) da instituição no Sistema de Pagamentos Brasileiro (SPB).
 *
 * Da documentação do Banco Central:
 *
 * > Número-Código: código identificador atribuído pelo Banco Central do Brasil às instituições participantes do STR.
 * > O número-código substituiu o antigo código COMPE.
 * > Todos os participantes do STR, exceto as Infraestruturas do Mercado Financeiro (IMF) e a Secretaria do Tesouro
 * >Nacional, possuem um número-código independentemente de participarem da Centralizadora da Compensação de Cheques
 * > (Compe). O campo tem a anotação “n/a” (“não se aplica”) para os participantes do STR aos quais não é
 * > atribuído um número-código;
 * >
 * > Fonte: https://www.bcb.gov.br/pom/spb/estatistica/port/ASTR003.pdf - acessado em 2022-08-21.
 */
class NumeroCodigo implements Stringable, JsonSerializable
{
    private const PATTERN_TO_MATCH_COMPE_CODE = '/^\d{3}$/';

    private function __construct(
        public readonly string $codigo,
    ) {
        if (strlen($codigo) === 0) {
            throw NumeroCodigoInvalido::paraStringVazia();
        }

        if (! preg_match(self::PATTERN_TO_MATCH_COMPE_CODE, $codigo, $matches)) {
            throw NumeroCodigoInvalido::paraStringNoFormatoInvalido($codigo);
        }
    }

    /**
     * @param non-empty-string $compeCode
     *
     * @throws NumeroCodigoInvalido
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
