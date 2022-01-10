<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use function sprintf;

final class BadLengthOutcome extends InvalidOutcome
{
    public function __construct(string $outcome)
    {
        parent::__construct(sprintf('Outcome %s is not 5 characters', $outcome));
    }
}
