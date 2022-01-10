<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use function sprintf;

final class NonAlphaGuess extends InvalidGuess
{
    public function __construct(string $guess)
    {
        parent::__construct(sprintf('Guess %s contains non-letters', $guess));
    }
}
