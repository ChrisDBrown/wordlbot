<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\AlreadySolved;
use App\Domain\Exception\HistoryLengthExceeded;

use function array_diff;
use function array_fill;
use function array_filter;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function range;
use function rtrim;
use function str_split;

use const PHP_EOL;

final class ResultHistory
{
    private const GRID_MAP = [
        Result::CHAR_ABSENT => '⬛',
        Result::CHAR_PRESENT => '🟨',
        Result::CHAR_CORRECT => '🟩',
    ];

    /** @var array<int, Result> */
    private array $results = [];

    public function addResult(Result $result): void
    {
        if ($this->isSolved()) {
            throw new AlreadySolved();
        }

        if (count($this->results) > 5) {
            throw new HistoryLengthExceeded();
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

    /** @return array<int, array<int, string>> */
    public function getKnownIncorrectLetterPositions(): array
    {
        $output = array_fill(0, 5, []);

        foreach ($this->results as $result) {
            for ($i = 0; $i < 5; $i++) {
                if ($result->getOutcome()[$i] !== Result::CHAR_PRESENT) {
                    continue;
                }

                $output[$i][] = $result->getGuess()[$i];
            }
        }

        for ($i = 0; $i < count($output); $i++) {
            $output[$i] = array_values(array_unique($output[$i]));
        }

        return $output;
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

    /** @return array<int, string> */
    public function getUnguessedLetters(): array
    {
        $knownLettersWithPositions = array_values(array_filter(str_split($this->getKnownLetterPositions()), static fn (string $letter) => $letter !== Result::CHAR_UNKNOWN));

        return array_values(array_diff(range('a', 'z'), $this->getKnownLetterMisses(), $this->getKnownLetterMatches(), $knownLettersWithPositions));
    }

    public function hasKnownLetters(): bool
    {
        return count($this->getKnownLetterMatches()) > 0 || $this->getKnownLetterPositions() !== implode(array_fill(0, 5, Result::CHAR_UNKNOWN));
    }

    public function isSolved(): bool
    {
        $resultCount = count($this->results);
        if ($resultCount === 0) {
            return false;
        }

        $lastResult = $this->results[$resultCount - 1];

        return $lastResult->getOutcome() === Result::FULLY_CORRECT;
    }

    public function getResultGrid(): string
    {
        $grid = '';
        foreach ($this->results as $result) {
            $row = '';
            foreach (str_split($result->getOutcome()) as $character) {
                $row .= self::GRID_MAP[$character];
            }

            $grid .= $row . PHP_EOL;
        }

        return rtrim($grid, PHP_EOL);
    }
}
