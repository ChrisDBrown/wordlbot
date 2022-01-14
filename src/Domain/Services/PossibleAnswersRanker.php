<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Exception\NoPossibleAnswers;
use App\Domain\Exception\NoPossibleScores;
use App\Domain\Exception\NoWinnerFound;
use App\Domain\Services\Interface\PossibleAnswersRanker as PossibleAnswersRankerInterface;

use function array_filter;
use function array_key_exists;
use function array_unique;
use function count;
use function in_array;
use function str_split;
use function strcmp;

use const ARRAY_FILTER_USE_KEY;

final class PossibleAnswersRanker implements PossibleAnswersRankerInterface
{
    /**
     * Scored based on the frequency of letters in the possible answers
     * See https://www3.nd.edu/~busiforc/handouts/cryptography/letterfrequencies.html
     */
    private const LETTER_SCORES = [
        'e' => 1233,
        'a' => 979,
        'r' => 899,
        'o' => 754,
        't' => 729,
        'l' => 719,
        'i' => 671,
        's' => 669,
        'n' => 575,
        'c' => 477,
        'u' => 467,
        'y' => 425,
        'd' => 393,
        'h' => 389,
        'p' => 367,
        'm' => 316,
        'g' => 311,
        'b' => 281,
        'f' => 230,
        'k' => 210,
        'w' => 159,
        'v' => 153,
        'z' => 40,
        'x' => 37,
        'q' => 29,
        'j' => 27,
    ];

    /**
     * @param array<int, string> $possibleAnswers
     * @param array<int, string> $unguessedLetters
     */
    public function getHighestRankingPossibleAnswer(array $possibleAnswers, array $unguessedLetters): string
    {
        if (count($possibleAnswers) === 0) {
            throw new NoPossibleAnswers();
        }

        $unguessedLetterScores = array_filter(self::LETTER_SCORES, static fn (string $letter) => in_array($letter, $unguessedLetters, true), ARRAY_FILTER_USE_KEY);

        if (count($unguessedLetterScores) === 0) {
            throw new NoPossibleScores();
        }

        $scores = [];

        foreach ($possibleAnswers as $possibleAnswer) {
            $scores[$possibleAnswer] = $this->calculateScore($possibleAnswer, $unguessedLetterScores);
        }

        $winner       = '';
        $winningScore = 0;

        foreach ($scores as $word => $score) {
            if ($score === $winningScore && ($winner === '' || strcmp($word, $winner) <= 0)) {
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

        if ($winner === '') {
            throw new NoWinnerFound();
        }

        return $winner;
    }

    /** @param array<string, int> $unguessedLetterScores */
    private function calculateScore(string $possibleAnswer, array $unguessedLetterScores): int
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
