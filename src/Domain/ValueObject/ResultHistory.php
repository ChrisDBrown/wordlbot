<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\HistoryLengthExceeded;
use App\Domain\ValueObject\Interface\ResultHistory as ResultHistoryInterface;

use function array_fill;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function implode;

final class ResultHistory implements ResultHistoryInterface
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

    public function getKnownLetterPositions(): string
    {
        $output = array_fill(0, 5, '.');

        foreach ($this->results as $result) {
            for ($i = 0; $i < 5; $i++) {
                if ($result->getKnownLetterPositions()[$i] === Result::CHAR_UNKNOWN) {
                    continue;
                }

                $output[$i] = $result->getKnownLetterPositions()[$i];
            }
        }

        return implode($output);
    }

    /** @return array<int, string> */
    public function getKnownLetterMatches(): array
    {
        $output = [];

        foreach ($this->results as $result) {
            $output = array_merge($output, $result->getKnownLetterMatches());
        }

        return array_values(array_unique($output));
    }

    /** @return array<int, string> */
    public function getKnownLetterMisses(): array
    {
        $output = [];

        foreach ($this->results as $result) {
            $output = array_merge($output, $result->getKnownLetterMisses());
        }

        return array_values(array_unique($output));
    }
}
