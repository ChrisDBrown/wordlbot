<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Exception\InvalidWordList;
use App\Application\Exception\MissingWordList;
use App\Application\Exception\UnopenableWordList;

use function count;
use function file_exists;
use function file_get_contents;
use function is_array;
use function json_decode;

final class PossibleAnswersProvider
{
    public function __construct(private string $filepath)
    {
    }

    /** @return array<int, string> */
    public function getAllPossibleAnswers(): array
    {
        if (! file_exists($this->filepath)) {
            throw new MissingWordList($this->filepath);
        }

        $fileContents = file_get_contents($this->filepath);

        if ($fileContents === false) {
            throw new UnopenableWordList($this->filepath);
        }

        $words = json_decode($fileContents);

        if (! is_array($words) || count($words) === 0) {
            throw new InvalidWordList($this->filepath);
        }

        return $words;
    }
}
