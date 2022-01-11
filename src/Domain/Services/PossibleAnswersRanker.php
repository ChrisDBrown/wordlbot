<?php

declare(strict_types=1);

namespace App\Domain\Services;

use function array_filter;
use function array_key_exists;
use function array_unique;
use function in_array;
use function str_split;
use function strcmp;

use const ARRAY_FILTER_USE_KEY;

final class PossibleAnswersRanker
{
    /**
     * Scored based on the frequency of letters in dictionary words
     * See https://www3.nd.edu/~busiforc/handouts/cryptography/letterfrequencies.html
     */
    private const LETTER_SCORES = [
        'e' => 56.88,
        'a' => 43.31,
        'r' => 38.64,
        'i' => 38.45,
        'o' => 36.51,
        't' => 35.43,
        'n' => 33.92,
        's' => 29.23,
        'l' => 27.98,
        'c' => 23.13,
        'u' => 18.51,
        'd' => 17.25,
        'p' => 16.14,
        'm' => 15.36,
        'h' => 15.31,
        'g' => 12.59,
        'b' => 10.56,
        'f' => 9.24,
        'y' => 9.06,
        'w' => 6.57,
        'k' => 5.61,
        'v' => 5.13,
        'x' => 1.48,
        'z' => 1.39,
        'j' => 1.00,
        'q' => 1.00,
    ];

    /**
     * @param array<int, string> $possibleAnswers
     * @param array<int, string> $unguessedLetters
     */
    public function getHighestRankingPossibleAnswer(array $possibleAnswers, array $unguessedLetters): string
    {
        $unguessedLetterScores = array_filter(self::LETTER_SCORES, static fn (string $letter) => in_array($letter, $unguessedLetters, true), ARRAY_FILTER_USE_KEY);

        $scores = [];

        foreach ($possibleAnswers as $possibleAnswer) {
            $scores[$possibleAnswer] = $this->calculateScore($possibleAnswer, $unguessedLetterScores);
        }

        $winner       = '';
        $winningScore = 0.0;

        foreach ($scores as $word => $score) {
            if ($score === $winningScore && strcmp($word, $winner) <= 0) {
                $winner       = $word;
                $winningScore = $score;

                continue;
            }

            if ($score <= $winningScore) {
                continue;
            }

            $winner       = $word;
            $winningScore = $score;
        }

        return $winner;
    }

    /** @param array<string, float> $unguessedLetterScores */
    private function calculateScore(string $possibleAnswer, array $unguessedLetterScores): float
    {
        $score = 0;

        $uniqueCharacters = array_unique(str_split($possibleAnswer));

        foreach ($uniqueCharacters as $letter) {
            if (! array_key_exists($letter, $unguessedLetterScores)) {
                continue;
            }

            $score += $unguessedLetterScores[$letter];
        }

        return $score;
    }
}
