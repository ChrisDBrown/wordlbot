<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Interface;

use App\Domain\ValueObject\Result;

interface ResultHistory
{
    /** @return array<int, Result> */
    public function getResults(): array;

    public function getKnownLetterPositions(): string;

    /** @return array<int, string> */
    public function getKnownLetterMatches(): array;

    /** @return array<int, string> */
    public function getKnownLetterMisses(): array;
}
