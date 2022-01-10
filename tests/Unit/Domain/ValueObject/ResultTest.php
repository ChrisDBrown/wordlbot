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
}
