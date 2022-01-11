<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\ValueObject\ResultHistory;

final class SuggestSolutionQuery
{
    public function __construct(private ResultHistory $resultHistory)
    {
    }

    public function getResultHistory(): ResultHistory
    {
        return $this->resultHistory;
    }
}
