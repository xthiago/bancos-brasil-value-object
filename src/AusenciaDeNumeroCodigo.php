<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use JsonSerializable;
use Stringable;

use function strlen;

/**
 * Representa a ausência de Número-Código (antigo Compe) da instituição no Sistema de Pagamentos Brasileiro (SPB).
 */
class AusenciaDeNumeroCodigo implements Stringable, JsonSerializable
{
    public readonly string $codigo;

    private function __construct()
    {
        $this->codigo = 'n/a';
    }

    public static function create(): self
    {
        return new self();
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
