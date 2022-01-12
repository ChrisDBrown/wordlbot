<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\HistoryLengthExceeded;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use Generator;
use PHPUnit\Framework\TestCase;

/** @covers ResultHistory */
final class ResultHistoryTest extends TestCase
{
    public function shouldAddResultsInOrder(): void
    {
        $resultHistory = new ResultHistory();

        $one   = new Result('beast', 'nnnnn');
        $two   = new Result('onion', 'nnnnn');
        $three = new Result('quarry', 'nnnnn');

        $resultHistory->addResult($three);
        $resultHistory->addResult($one);
        $resultHistory->addResult($two);

        self::assertSame([$three, $one, $two], $resultHistory->getResults());
    }

    /** @test */
    public function shouldErrorOnAddingTooManyResults(): void
    {
        $resultHistory = new ResultHistory();

        $resultHistory->addResult(new Result('beast', 'nnnnn'));
        $resultHistory->addResult(new Result('onion', 'nnnnn'));
        $resultHistory->addResult(new Result('quart', 'nnnnn'));
        $resultHistory->addResult(new Result('video', 'nnnnn'));
        $resultHistory->addResult(new Result('chair', 'nnnnn'));
        $resultHistory->addResult(new Result('favor', 'nnnnn'));

        self::expectException(HistoryLengthExceeded::class);
        $resultHistory->addResult(new Result('train', 'nnnnn'));
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
                new Result('beast', 'nnnnn'),
                new Result('fires', 'nnnnn'),
            ],
            '.....',
        ];

        yield 'History with matches' => [
            [
                new Result('beast', 'pppln'),
                new Result('bears', 'ppppp'),
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
                new Result('beast', 'nnnnn'),
                new Result('fires', 'nnnnn'),
            ],
            [],
        ];

        yield 'History with matches' => [
            [
                new Result('beast', 'nnnln'),
                new Result('bears', 'nlnnl'),
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
                new Result('first', 'pllll'),
                new Result('frits', 'ppppp'),
            ],
            [],
        ];

        yield 'History with misses' => [
            [
                new Result('beast', 'nnnln'),
                new Result('storm', 'pnnnn'),
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
                new Result('abcde', 'nnnnn'),
                new Result('fghij', 'nnnnn'),
                new Result('klmno', 'nnnnn'),
                new Result('pqrst', 'nnnnn'),
                new Result('uvwxy', 'nnnnn'),
            ],
            ['z'],
        ];

        yield 'Some unguessed letters' => [
            [
                new Result('abcde', 'nnnnn'),
                new Result('fghij', 'nnnnn'),
                new Result('klmno', 'nnnnn'),
                new Result('uvwxy', 'nnnnn'),
            ],
            ['p', 'q', 'r', 's', 't', 'z'],
        ];

        yield 'Some unguessed letters, mix of outcomes' => [
            [
                new Result('beast', 'nnpll'),
                new Result('stars', 'pppnn'),
                new Result('stand', 'pppln'),
                new Result('stain', 'ppppp'),
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
                new Result('abcde', 'nnnnn'),
                new Result('fghij', 'nnnnn'),
                new Result('klmno', 'nnnnn'),
                new Result('pqrst', 'nnnnn'),
                new Result('uvwxy', 'nnnnn'),
            ],
            false,
        ];

        yield 'No known letters, no guesses' => [
            [],
            false,
        ];

        yield 'Known letters, position' => [
            [
                new Result('beast', 'pppnn'),
                new Result('bears', 'pppnn'),
            ],
            true,
        ];

        yield 'Known letters, existing' => [
            [
                new Result('beast', 'nnnnn'),
                new Result('bears', 'nnnln'),
            ],
            true,
        ];

        yield 'Known letters, mix' => [
            [
                new Result('beast', 'plnnn'),
                new Result('bears', 'plnln'),
            ],
            true,
        ];
    }
}
