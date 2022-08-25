<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use DateTimeImmutable;
use JsonSerializable;
use Stringable;

use function strlen;

/**
 * Representa uma instituição financeira brasileira.
 *
 * O *código do banco* é o *Código do Sistema de Operações Monetárias e Compensação de Outros Papéis (Compe)* no
 * *Sistema Financeiro Nacional* do Banco Central do Brasil.
 *
 * Também conhecido como `código compe`.
 */
class InstituicaoFinanceira implements Stringable, JsonSerializable
{
    private static ?InstituicaoFinanceiraFactory $factory = null;

    /**
     * Cria uma instância de banco a partir dos dados fornecidos.
     *
     * Use os construtores nomeados (métodos estáticos) que esta classe fornece:
     *
     * <code>
     *  use Xthiago\ValueObject\BancosBrasil;
     *
     *  $bradesco = BankCode::fromString('237');
     *  $itau = BankCode::fromString('341');
     * </code>
     */
    private function __construct(
        public readonly ?NumeroCodigo $codigo = null, // AusenciaDeNumeroCodigo?
        public readonly ISPB $ispb,
        public readonly string $nome,
        public readonly string $nomeCurto,
        public readonly bool $participaDaCompe,
        public readonly string $acessoPrincipal,
        public readonly DateTimeImmutable $inicioDaOperacao,
    ) {
    }

    /**
     * @param non-empty-string $bankCode
     *
     * @throws NumeroCodigoInvalido|BancoNaoEncontrado ver BancoFactory.
     */
    public static function fromString(string $codigoDoBanco): self
    {
        return self::getFactory()->fromString($codigoDoBanco);
    }

    /**
     * Semelhante ao self::fromString(), mas retorna `null` ao invés de emitir BankNotFound.
     *
     * @param non-empty-string $bankCode
     *
     * @throws NumeroCodigoInvalido
     */
    public static function tryFromString(string $codigoDoBanco): ?self
    {
        try {
            return self::getFactory()->fromString($codigoDoBanco);
        } catch (BancoNaoEncontrado $exception) {
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
    public static function fromFactory(
        ?NumeroCodigo $codigo = null,
        ISPB $ispb,
        string $nome,
        string $nomeCurto,
        bool $participaDaCompe,
        string $acessoPrincipal,
        DateTimeImmutable $inicioDaOperacao,
    ): self
    {
        return new self(
            $codigo,
            $ispb,
            $nome,
            $nomeCurto,
            $participaDaCompe,
            $acessoPrincipal,
            $inicioDaOperacao,
        );
    }

    /** @return array{code: string, name: string} */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->codigo,
            'name' => $this->nome,
        ];
    }

    public function __toString(): string
    {
        return $this->codigo->compe;
    }

    public function isEqualTo(InstituicaoFinanceira $banco): bool
    {
        return $this->codigo === $banco->codigo;
    }

    public function equals(?object $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->codigo === $other->codigo;
    }

    /**
     * Define a fábrica que é usada para criar as instâncias do VO a partir da string fornecida (código).
     */
    public static function setFactory(InstituicaoFinanceiraFactory $factory): void
    {
        self::$factory = $factory;
    }

    /**
     * Retorna a fábrica que é usada para criar as instâncias do VO a partir da string fornecida (código).
     */
    public static function getFactory(): InstituicaoFinanceiraFactory
    {
        if (self::$factory === null) {
            self::$factory = FromArrayFactory::withDefaultConfiguration();
        }

        return self::$factory;
    }

    /**
     * Restaura a fábrica padrão da biblioteca.
     */
    public static function resetFactory(): void
    {
        self::$factory = null;
    }
}
