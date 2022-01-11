<?php

declare(strict_types=1);

namespace App\Application\Exception;

use function sprintf;

final class MissingWordList extends CannotLoadWordList
{
    public function __construct(string $filepath)
    {
        parent::__construct(sprintf('Word list file %s does not exist', $filepath));
    }
}
