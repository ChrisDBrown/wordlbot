<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\BadLengthGuess;
use App\Domain\Exception\BadLengthOutcome;
use App\Domain\Exception\InvalidCharactersOutcome;
use App\Domain\Exception\NonAlphaGuess;
use App\Domain\ValueObject\Result;
use PHPUnit\Framework\TestCase;

/** @covers Result */
final class ResultTest extends TestCase
{
    /** @test */
    public function shouldLowercaseGetterValues(): void
    {
        $result = new Result('BEAST', 'PNPNP');

        self::assertSame('beast', $result->getGuess());
        self::assertSame('pnpnp', $result->getOutcome());
    }

    /**
     * @test
     * @dataProvider badLengthGuesses
     */
    public function shouldThrowOnBadLengthGuesses(string $guess): void
    {
        self::expectException(BadLengthGuess::class);
        new Result($guess, 'nnnnn');
    }

    /** @return array<int, array<int, string>> */
    public function badLengthGuesses(): array
    {
        return [
            ['g'],
            ['gggggggg'],
            [''],
            ['        '],
        ];
    }

    /**
     * @test
     * @dataProvider badCharacterGuesses
     */
    public function shouldThrowOnBadCharacterGuesses(string $guess): void
    {
        self::expectException(NonAlphaGuess::class);
        new Result($guess, 'nnnnn');
    }

    /** @return array<int, array<int, string>> */
    public function badCharacterGuesses(): array
    {
        return [
            ['bea11'],
            ['be as'],
            ['.....'],
            ['béast'],
        ];
    }

    /**
     * @test
     * @dataProvider badLengthOutcomes
     */
    public function shouldThrownOnBadLengthOutcomes(string $outcome): void
    {
        self::expectException(BadLengthOutcome::class);
        new Result('beast', $outcome);
    }

    /** @return array<int, array<int, string>> */
    public function badLengthOutcomes(): array
    {
        return [
            ['p'],
            ['pppppppp'],
            [''],
            ['        '],
        ];
    }

    /**
     * @test
     * @dataProvider badCharacterOutcomes
     */
    public function shouldThrowOnBadCharacterOutcomes(string $outcome): void
    {
        self::expectException(InvalidCharactersOutcome::class);
        new Result('beast', $outcome);
    }

    /** @return array<int, array<int, string>> */
    public function badCharacterOutcomes(): array
    {
        return [
            ['abcde'],
            ['n l p'],
            ['ñññññ'],
            ['n.l.p'],
        ];
    }

    /**
     * @test
     * @dataProvider knownLetterPositions
     */
    public function shouldReturnKnownLetterPositions(string $guess, string $outcome, string $expected): void
    {
        $result = new Result($guess, $outcome);

        self::assertSame($expected, $result->getKnownLetterPositions());
    }

    /** @return array<int, array<int, string>> */
    public function knownLetterPositions(): array
    {
        return [
            ['beast', 'nlppl', '..as.'],
            ['beast', 'nnnnn', '.....'],
            ['beast', 'lllll', '.....'],
            ['beast', 'ppppp', 'beast'],
        ];
    }

    /**
     * @param array<int, string> $expected
     *
     * @test
     * @dataProvider knownLetterMatches
     */
    public function shouldReturnKnownLetterMatches(string $guess, string $outcome, array $expected): void
    {
        $result = new Result($guess, $outcome);

        self::assertSame($expected, $result->getKnownLetterMatches());
    }

    /** @return array<int, array<int, array<int, string>|string>> */
    public function knownLetterMatches(): array
    {
        return [
            ['beast', 'nlppl', ['e', 't']],
            ['beast', 'nnnnn', []],
            ['beast', 'lllll', ['b', 'e', 'a', 's', 't']],
            ['beast', 'ppppp', []],
            ['aaaaa', 'lllll', ['a']],
            ['baaaa', 'nllll', ['a']],
        ];
    }

    /**
     * @param array<int, string> $expected
     *
     * @test
     * @dataProvider knownLetterMisses
     */
    public function shouldReturnKnownLetterMisses(string $guess, string $outcome, array $expected): void
    {
        $result = new Result($guess, $outcome);

        self::assertSame($expected, $result->getKnownLetterMisses());
    }

    /** @return array<int, array<int, array<int, string>|string>> */
    public function knownLetterMisses(): array
    {
        return [
            ['beast', 'nlppl', ['b']],
            ['beast', 'nnnnn', ['b', 'e', 'a', 's', 't']],
            ['beast', 'lllll', []],
            ['beast', 'ppppp', []],
            ['baaaa', 'lnnnn', ['a']],
        ];
    }
}
