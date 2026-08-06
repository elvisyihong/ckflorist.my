<?php

declare(strict_types=1);

namespace App\Services;

final class ValidationException extends \RuntimeException
{
    public function __construct(public readonly array $errors)
    {
        parent::__construct('The submitted information needs attention.');
    }
}

