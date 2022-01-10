<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use function sprintf;

final class BadLengthGuess extends InvalidGuess
{
    public function __construct(string $guess)
    {
        parent::__construct(sprintf('Guess %s is not 5 characters', $guess));
    }
}
