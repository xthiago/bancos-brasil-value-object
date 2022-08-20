<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

use function strlen;

class Banco implements Stringable, JsonSerializable
{
    private static ?BancoFactory $factory = null;

    /**
     * Cria uma instância de banco a partir dos dados fornecidos.
     *
     * Use os construtores nomeados (métodos estáticos) que esta classe fornece:
     *
     * ```
     *  use Xthiago\ValueObject\BancosBrasil;
     *
     *  $bradesco = Banco::fromString('237');
     *  $itau = Banco::fromString('341');
     *  $banco = Banco::fromString($inputDoUsuario);
     * ```
     */
    private function __construct(
        public readonly string $code,
        public readonly string $name,
    ) {
        if (strlen($code) === 0) {
            throw InvalidBankCode::forEmptyCode();
        }
    }

    /**
     * @param non-empty-string $bankCode
     *
     * @throws InvalidBankCode|BankNotFound ver BancoFactory.
     */
    public static function fromString(string $bankCode): self
    {
        return self::getFactory()->fromString($bankCode);
    }

    /**
     * Semelhante ao self::fromString(), mas retorna `null` ao invés de emitir BankNotFound.
     *
     * @param non-empty-string $bankCode
     *
     * @throws InvalidBankCode
     */
    public static function tryFromString(string $bankCode): ?self
    {
        try {
            return self::getFactory()->fromString($bankCode);
        } catch (InvalidArgumentException $exception) {
            return null;
        }
    }

    /**
     * NÃO USE este construtor diretamente. Ele NÃO verifica se os dados estão corretos.
     *
     * Ele deve ser usado apenas se você estiver criando sua própria Factory. Nesse caso as factories são responsáveis
     * por validar os dados (Ex: código `237` é do `Banco Bradesco S.A.` e não do `Banco do Brasil` (`001`).
     *
     * @param non-empty-string $code
     */
    public static function fromFactory(string $code, string $name): self
    {
        return new self($code, $name);
    }

    /** @return array{code: string, name: string} */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
        ];
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function isEqualTo(Banco $banco): bool
    {
        return $this->code === $banco->code;
    }

    public function equals(?object $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->code === $other->code;
    }

    /**
     * Define a fábrica que é usada para criar as instâncias do VO a partir da string fornecida (código).
     */
    public static function setFactory(BancoFactory $factory): void
    {
        self::$factory = $factory;
    }

    /**
     * Retorna a fábrica que é usada para criar as instâncias do VO a partir da string fornecida (código).
     */
    public static function getFactory(): BancoFactory
    {
        if (self::$factory === null) {
            self::$factory = ArrayFactory::withDefaultConfiguration();
        }

        return self::$factory;
    }

    public static function resetFactory(): void
    {
        self::$factory = null;
    }
}
