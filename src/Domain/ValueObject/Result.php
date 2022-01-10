<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\BadLengthGuess;
use App\Domain\Exception\BadLengthOutcome;
use App\Domain\Exception\InvalidCharactersOutcome;
use App\Domain\Exception\NonAlphaGuess;

use function ctype_lower;
use function mb_strlen;
use function str_replace;
use function strtolower;

final class Result
{
    public const CHAR_NO_MATCH       = 'n';
    public const CHAR_LETTER_MATCH   = 'l';
    public const CHAR_POSITION_MATCH = 'p';
    public const VALID_OUTCOME_CHARS = [
        self::CHAR_NO_MATCH,
        self::CHAR_LETTER_MATCH,
        self::CHAR_POSITION_MATCH,
    ];

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
}
