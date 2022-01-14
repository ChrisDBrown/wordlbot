<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\BadLengthGuess;
use App\Domain\Exception\BadLengthOutcome;
use App\Domain\Exception\InvalidCharactersOutcome;
use App\Domain\Exception\NonAlphaGuess;

use function array_fill;
use function array_unique;
use function array_values;
use function ctype_lower;
use function implode;
use function mb_strlen;
use function str_replace;
use function strtolower;

final class Result
{
    public const CHAR_ABSENT         = 'a';
    public const CHAR_PRESENT        = 'p';
    public const CHAR_CORRECT        = 'c';
    public const CHAR_UNKNOWN        = '.';
    public const VALID_OUTCOME_CHARS = [
        self::CHAR_ABSENT,
        self::CHAR_PRESENT,
        self::CHAR_CORRECT,
    ];
    public const FULLY_CORRECT       = 'ccccc';

    private string $guess;
    private string $outcome;

    public function __construct(
        string $guess,
        string $outcome
    ) {
        $guess   = strtolower($guess);
        $outcome = strtolower($outcome);

        if (mb_strlen($guess) !== 5) {
            throw new BadLengthGuess($guess);
        }

        if (ctype_lower($guess) !== true) {
            throw new NonAlphaGuess($guess);
        }

        if (mb_strlen($outcome) !== 5) {
            throw new BadLengthOutcome($outcome);
        }

        if (mb_strlen(str_replace(self::VALID_OUTCOME_CHARS, '', $outcome)) !== 0) {
            throw new InvalidCharactersOutcome($outcome);
        }

        $this->guess   = $guess;
        $this->outcome = $outcome;
    }

    public function getGuess(): string
    {
        return $this->guess;
    }

    public function getOutcome(): string
    {
        return $this->outcome;
    }

    public function getKnownLetterPositions(): string
    {
        $output = array_fill(0, 5, self::CHAR_UNKNOWN);

        for ($i = 0; $i < 5; $i++) {
            if ($this->outcome[$i] !== self::CHAR_CORRECT) {
                continue;
            }

            $output[$i] = $this->guess[$i];
        }

        return implode($output);
    }

    /** @return array<int, string> */
    public function getKnownLetterMatches(): array
    {
        $output = [];

        for ($i = 0; $i < 5; $i++) {
            if ($this->outcome[$i] !== self::CHAR_PRESENT) {
                continue;
            }

            $output[] = $this->guess[$i];
        }

        return array_values(array_unique($output));
    }

    /** @return array<int, string> */
    public function getKnownLetterMisses(): array
    {
        $output = [];

        for ($i = 0; $i < 5; $i++) {
            if ($this->outcome[$i] !== self::CHAR_ABSENT) {
                continue;
            }

            $output[] = $this->guess[$i];
        }

        return array_values(array_unique($output));
    }
}
