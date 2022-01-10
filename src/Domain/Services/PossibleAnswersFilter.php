<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\ValueObject\Interface\ResultHistory;
use App\Domain\ValueObject\Result;

use function array_filter;
use function array_values;
use function str_contains;

final class PossibleAnswersFilter
{
    /**
     * @param array<int, string> $possibleAnswers
     *
     * @return array<int, string>
     */
    public function getValidAnswersForHistory(array $possibleAnswers, ResultHistory $resultHistory): array
    {
        $validAnswers = $this->filterAnswersNotMatchingKnownPositions($possibleAnswers, $resultHistory->getKnownLetterPositions());
        $validAnswers = $this->filterAnswersContainingKnownMisses($validAnswers, $resultHistory->getKnownLetterMisses());

        return $this->filterAnswersNotContainingKnownMatches($validAnswers, $resultHistory->getKnownLetterMatches());
    }

    /**
     * @param array<int, string> $possibleAnswers
     *
     * @return array<int, string>
     */
    private function filterAnswersNotMatchingKnownPositions(array $possibleAnswers, string $knownPositions): array
    {
        return array_values(array_filter($possibleAnswers, static function (string $answer) use ($knownPositions) {
            for ($i = 0; $i < 5; $i++) {
                if ($knownPositions[$i] !== Result::CHAR_UNKNOWN) {
                    continue;
                }

                $answer[$i] = Result::CHAR_UNKNOWN;
            }

            return $answer === $knownPositions;
        }));
    }

    /**
     * @param array<int, string> $possibleAnswers
     * @param array<int, string> $misses
     *
     * @return array<int, string>
     */
    private function filterAnswersContainingKnownMisses(array $possibleAnswers, array $misses): array
    {
        return array_values(array_filter($possibleAnswers, static function (string $answer) use ($misses) {
            foreach ($misses as $miss) {
                if (str_contains($answer, $miss)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @param array<int, string> $possibleAnswers
     * @param array<int, string> $matches
     *
     * @return array<int, string>
     */
    private function filterAnswersNotContainingKnownMatches(array $possibleAnswers, array $matches): array
    {
        return array_values(array_filter($possibleAnswers, static function (string $answer) use ($matches) {
            foreach ($matches as $match) {
                if (! str_contains($answer, $match)) {
                    return false;
                }
            }

            return true;
        }));
    }
}
