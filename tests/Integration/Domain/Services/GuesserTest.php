<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Services;

use App\Domain\Services\Guesser;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function assert;

final class GuesserTest extends KernelTestCase
{
    private Guesser $guesser;

    public function setUp(): void
    {
        self::bootKernel();

        $guesser = static::getContainer()->get(Guesser::class);
        assert($guesser instanceof Guesser);

        $this->guesser = $guesser;

        parent::setUp();
    }

    /** @test */
    public function shouldReturnDefaultGuessOnFirstAttempt(): void
    {
        $resultHistory = new ResultHistory();

        $actual = $this->guesser->guess($resultHistory);

        self::assertSame('beast', $actual);
    }

    /** @test */
    public function shouldReturnDefaultGuessOnSecondAttemptIfNoMatches(): void
    {
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('beast', 'nnnnn'));

        $actual = $this->guesser->guess($resultHistory);

        self::assertSame('round', $actual);
    }

    /** @test */
    public function shouldReturnDefaultGuessOnThirdAttemptIfNoMatches(): void
    {
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('beast', 'nnnnn'));
        $resultHistory->addResult(new Result('round', 'nnnnn'));

        $actual = $this->guesser->guess($resultHistory);

        self::assertSame('lymph', $actual);
    }

    /**
     * @param array<int, Result> $results
     *
     * @test
     * @dataProvider guesses
     */
    public function shouldReturnGuess(array $results, string $expected): void
    {
        $resultHistory = new ResultHistory();
        foreach ($results as $result) {
            $resultHistory->addResult($result);
        }

        $actual = $this->guesser->guess($resultHistory);

        self::assertSame($expected, $actual);
    }

    /** @return Generator<string, array{0: array<int, Result>, 1: string}, void, void> */
    public function guesses(): Generator
    {
        yield 'No history' => [
            [],
            'beast',
        ];

        yield 'One history, no matches' => [
            [
                new Result('beast', 'nnnnn'),
            ],
            'round',
        ];

        yield 'Two history, no matches' => [
            [
                new Result('beast', 'nnnnn'),
                new Result('round', 'nnnnn'),
            ],
            'lymph',
        ];

        yield 'Two history, some matches' => [
            [
                new Result('beast', 'nnnnn'),
                new Result('round', 'lnnpl'),
                new Result('grind', 'npppl'),
            ],
            'drink',
        ];
    }
}
