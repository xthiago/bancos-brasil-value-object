<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use InvalidArgumentException;

use function sprintf;

class BankNotFound extends InvalidArgumentException
{
    private function __construct(
        string $message,
        public readonly string $bankCode,
    ) {
        parent::__construct($message);
    }

    public static function forCode(string $bankCode): self
    {
        return new self(
            message: sprintf('There is no bank with given code ("%s")', $bankCode),
            bankCode: $bankCode
        );
    }
}
