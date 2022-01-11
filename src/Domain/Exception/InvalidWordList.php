<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use function sprintf;

final class InvalidWordList extends CannotLoadWordList
{
    public function __construct(string $filepath)
    {
        parent::__construct(sprintf('Word list file %s is empty or invalid', $filepath));
    }
}
