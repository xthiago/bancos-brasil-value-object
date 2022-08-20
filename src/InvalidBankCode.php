<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\BancosBrasil;

use InvalidArgumentException;

class InvalidBankCode extends InvalidArgumentException
{
    private function __construct(
        string $message,
        public readonly string $bankCode,
    ) {
        parent::__construct($message);
    }

    public static function forEmptyCode(): self
    {
        return new self(
            message: 'Bank code should be a non empty string.',
            bankCode: '',
        );
    }
}
