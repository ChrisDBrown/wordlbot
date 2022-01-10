<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\HistoryLengthExceeded;

use function count;

final class ResultHistory
{
    /** @var array<int, Result> */
    private array $results = [];

    public function addResult(Result $result): void
    {
        if (count($this->results) >= 5) {
            throw new HistoryLengthExceeded('Cannot add result to history, as it\'s already full');
        }

        $this->results[] = $result;
    }

    /** @return array<int, Result> */
    public function getResults(): array
    {
        return $this->results;
    }
}
