<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use InvalidArgumentException;

final class FilterReturnsEmpty extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('No possible answers left after applying result history filters');
    }
}
