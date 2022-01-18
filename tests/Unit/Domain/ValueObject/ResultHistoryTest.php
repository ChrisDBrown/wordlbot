<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\AlreadySolved;
use App\Domain\Exception\HistoryLengthExceeded;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use Generator;
use PHPUnit\Framework\TestCase;

use const PHP_EOL;

/** @covers ResultHistory */
final class ResultHistoryTest extends TestCase
{
    public function shouldAddResultsInOrder(): void
    {
        $resultHistory = new ResultHistory();

        $one   = new Result('beast', 'aaaaa');
        $two   = new Result('onion', 'aaaaa');
        $three = new Result('quarry', 'aaaaa');

        $resultHistory->addResult($three);
        $resultHistory->addResult($one);
        $resultHistory->addResult($two);

        self::assertSame([$three, $one, $two], $resultHistory->getResults());
    }

    /** @test */
    public function shouldErrorOnAddingTooManyResults(): void
    {
        $resultHistory = new ResultHistory();

        $resultHistory->addResult(new Result('beast', 'aaaaa'));
        $resultHistory->addResult(new Result('onion', 'aaaaa'));
        $resultHistory->addResult(new Result('quart', 'aaaaa'));
        $resultHistory->addResult(new Result('video', 'aaaaa'));
        $resultHistory->addResult(new Result('chair', 'aaaaa'));
        $resultHistory->addResult(new Result('favor', 'aaaaa'));

        self::expectException(HistoryLengthExceeded::class);
        $resultHistory->addResult(new Result('train', 'aaaaa'));
    }

    /**
     * @param array<int, Result> $results
     *
     * @test
     * @dataProvider knownLetterPositions
     */
    public function shouldReturnKnownLetterPositions(array $results, string $expected): void
    {
        $resultHistory = new ResultHistory();

        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        self::assertSame($expected, $resultHistory->getKnownLetterPositions());
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: string}, void, void> */
    public function knownLetterPositions(): Generator
    {
        yield 'No history' => [
            [],
            '.....',
        ];

        yield 'History with no matches' => [
            [
                new Result('beast', 'aaaaa'),
                new Result('fires', 'aaaaa'),
            ],
            '.....',
        ];

        yield 'History with matches' => [
            [
                new Result('beast', 'cccpa'),
                new Result('bears', 'ccccc'),
            ],
            'bears',
        ];
    }

    /**
     * @param array<int, Result> $results
     * @param array<int, string> $expected
     *
     * @test
     * @dataProvider knownLetterMatches
     */
    public function shouldReturnKnownLetterMatches(array $results, array $expected): void
    {
        $resultHistory = new ResultHistory();

        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        self::assertSame($expected, $resultHistory->getKnownLetterMatches());
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: array<int, string>}, void, void> */
    public function knownLetterMatches(): Generator
    {
        yield 'No history' => [
            [],
            [],
        ];

        yield 'History with no matches' => [
            [
                new Result('beast', 'aaaaa'),
                new Result('fires', 'aaaaa'),
            ],
            [],
        ];

        yield 'History with matches' => [
            [
                new Result('beast', 'aaapa'),
                new Result('bears', 'apaap'),
            ],
            ['s', 'e'],
        ];
    }

    /**
     * @param array<int, Result> $results
     * @param array<int, string> $expected
     *
     * @test
     * @dataProvider knownLetterMisses
     */
    public function shouldReturnKnownLetterMisses(array $results, array $expected): void
    {
        $resultHistory = new ResultHistory();

        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        self::assertSame($expected, $resultHistory->getKnownLetterMisses());
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: array<int, string>}, void, void> */
    public function knownLetterMisses(): Generator
    {
        yield 'No history' => [
            [],
            [],
        ];

        yield 'History with no misses' => [
            [
                new Result('first', 'cpppp'),
                new Result('frits', 'ccccc'),
            ],
            [],
        ];

        yield 'History with misses' => [
            [
                new Result('beast', 'aaapa'),
                new Result('storm', 'caaaa'),
            ],
            ['b', 'e', 'a', 't', 'o', 'r', 'm'],
        ];
    }

    /**
     * @param array<int, Result> $results
     * @param array<int, string> $expected
     *
     * @test
     * @dataProvider unguessedLetters
     */
    public function shouldReturnUnguessedLetters(array $results, array $expected): void
    {
        $resultHistory = new ResultHistory();

        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        self::assertSame($expected, $resultHistory->getUnguessedLetters());
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: array<int, string>}, void, void> */
    public function unguessedLetters(): Generator
    {
        yield 'One unguessed letters' => [
            [
                new Result('abcde', 'aaaaa'),
                new Result('fghij', 'aaaaa'),
                new Result('klmno', 'aaaaa'),
                new Result('pqrst', 'aaaaa'),
                new Result('uvwxy', 'aaaaa'),
            ],
            ['z'],
        ];

        yield 'Some unguessed letters' => [
            [
                new Result('abcde', 'aaaaa'),
                new Result('fghij', 'aaaaa'),
                new Result('klmno', 'aaaaa'),
                new Result('uvwxy', 'aaaaa'),
            ],
            ['p', 'q', 'r', 's', 't', 'z'],
        ];

        yield 'Some unguessed letters, mix of outcomes' => [
            [
                new Result('beast', 'aacpp'),
                new Result('stars', 'cccaa'),
                new Result('stand', 'cccpa'),
                new Result('stain', 'ccccc'),
            ],
            ['c', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'o', 'p', 'q', 'u', 'v', 'w', 'x', 'y', 'z'],
        ];
    }

    /**
     * @param array<int, Result> $results
     *
     * @test
     * @dataProvider hasKnownLetters
     */
    public function shouldReturnHasKnownLetters(array $results, bool $expected): void
    {
        $resultHistory = new ResultHistory();

        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        self::assertSame($expected, $resultHistory->hasKnownLetters());
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: bool}, void, void> */
    public function hasKnownLetters(): Generator
    {
        yield 'No known letters, many guesses' => [
            [
                new Result('abcde', 'aaaaa'),
                new Result('fghij', 'aaaaa'),
                new Result('klmno', 'aaaaa'),
                new Result('pqrst', 'aaaaa'),
                new Result('uvwxy', 'aaaaa'),
            ],
            false,
        ];

        yield 'No known letters, no guesses' => [
            [],
            false,
        ];

        yield 'Known letters, position' => [
            [
                new Result('beast', 'cccaa'),
                new Result('bears', 'cccaa'),
            ],
            true,
        ];

        yield 'Known letters, existing' => [
            [
                new Result('beast', 'aaaaa'),
                new Result('bears', 'aaapa'),
            ],
            true,
        ];

        yield 'Known letters, mix' => [
            [
                new Result('beast', 'cpaaa'),
                new Result('bears', 'cpapa'),
            ],
            true,
        ];
    }

    /**
     * @param array<int, Result> $results
     *
     * @test
     * @dataProvider isSolved
     */
    public function shouldReturnIsSolved(array $results, bool $expected): void
    {
        $resultHistory = new ResultHistory();

        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        self::assertSame($expected, $resultHistory->isSolved());
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: bool}, void, void> */
    public function isSolved(): Generator
    {
        yield 'Not solved, no guesses' => [
            [],
            false,
        ];

        yield 'Not solved, no known letters' => [
            [
                new Result('abcde', 'aaaaa'),
                new Result('fghij', 'aaaaa'),
                new Result('klmno', 'aaaaa'),
                new Result('pqrst', 'aaaaa'),
                new Result('uvwxy', 'aaaaa'),
            ],
            false,
        ];

        yield 'Not solved, some known letters' => [
            [
                new Result('beast', 'cccaa'),
                new Result('bears', 'cccaa'),
            ],
            false,
        ];

        yield 'Not solved, one known letter' => [
            [
                new Result('beast', 'aaaaa'),
                new Result('bears', 'aaapa'),
            ],
            false,
        ];

        yield 'Solved, multiple guesses' => [
            [
                new Result('beast', 'cccpa'),
                new Result('bears', 'ccccc'),
            ],
            true,
        ];

        yield 'Solved, one magic guess' => [
            [
                new Result('beast', 'ccccc'),
            ],
            true,
        ];
    }

    /** @test */
    public function shouldThrowOnTryingToAddToASolvedHistory(): void
    {
        $resultHistory = new ResultHistory();

        $resultHistory->addResult(new Result('beast', 'ccccc'));

        self::expectException(AlreadySolved::class);
        $resultHistory->addResult(new Result('bingo', 'aaaaa'));
    }

    /**
     * @param array<int, Result> $results
     *
     * @test
     * @dataProvider resultGrid
     */
    public function shouldBuildOutputGrid(array $results, string $expected): void
    {
        $resultHistory = new ResultHistory();

        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        self::assertSame($expected, $resultHistory->getResultGrid());
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: string}, void, void> */
    public function resultGrid(): Generator
    {
        yield 'No results, empty grid' => [
            [],
            '',
        ];

        yield 'One result, one row' => [
            [
                new Result('beast', 'capcc'),
            ],
            '🟩⬛🟨🟩🟩',
        ];

        yield 'Four results, four rows' => [
            [
                new Result('beast', 'aacpp'),
                new Result('stars', 'cccaa'),
                new Result('stand', 'cccpa'),
                new Result('stain', 'ccccc'),
            ],
            '⬛⬛🟩🟨🟨' . PHP_EOL . '🟩🟩🟩⬛⬛' . PHP_EOL . '🟩🟩🟩🟨⬛' . PHP_EOL . '🟩🟩🟩🟩🟩',
        ];
    }
}
