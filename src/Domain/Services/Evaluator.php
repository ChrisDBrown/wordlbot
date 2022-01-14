<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\ValueObject\Result;

use function array_fill;
use function implode;
use function str_split;
use function strpos;

final class Evaluator
{
    public function evaluate(string $guess, string $answer): string
    {
        if ($guess === $answer) {
            return Result::FULLY_CORRECT;
        }

        $guess  = str_split($guess);
        $answer = str_split($answer);

        $outcome = array_fill(0, 5, Result::CHAR_ABSENT);

        for ($i = 0; $i < 5; $i++) {
            if ($guess[$i] !== $answer[$i]) {
                continue;
            }

            $outcome[$i] = Result::CHAR_CORRECT;
        }

        for ($i = 0; $i < 5; $i++) {
            $pos = strpos(implode('', $guess), $answer[$i]);
            if ($pos === false || $outcome[$pos] === Result::CHAR_CORRECT) {
                continue;
            }

            $outcome[$pos] = Result::CHAR_PRESENT;
            $guess[$pos]   = '.';
        }

        return implode('', $outcome);
    }
}
