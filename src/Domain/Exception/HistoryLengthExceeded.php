<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use OutOfBoundsException;

final class HistoryLengthExceeded extends OutOfBoundsException
{
    public function __construct()
    {
        parent::__construct('Cannot add result to history, as it\'s already full');
    }
}
