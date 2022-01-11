<?php

declare(strict_types=1);

namespace App\Application\Exception;

use function sprintf;

final class UnopenableWordList extends CannotLoadWordList
{
    public function __construct(string $filepath)
    {
        parent::__construct(sprintf('Cannot open word list file %s', $filepath));
    }
}
