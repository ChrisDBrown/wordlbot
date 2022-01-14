<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Services;

use App\Domain\Exception\NoPossibleAnswers;
use App\Domain\Exception\NoPossibleScores;
use App\Domain\Exception\NoWinnerFound;
use App\Domain\Services\PossibleAnswersRanker;
use PHPUnit\Framework\TestCase;

use function range;

/** @covers PossibleAnswersRanker */
final class PossibleAnswersRankerTest extends TestCase
{
    private PossibleAnswersRanker $ranker;

    public function setUp(): void
    {
        $this->ranker = new PossibleAnswersRanker();

        parent::setUp();
    }

    /** @test */
    public function shouldReturnHighestScoreFromList(): void
    {
        $possibleAnswers = [
            'steer', // 3530
            'civil', // 2020
            'bride', // 3477
            'beast', // 3891
            'ghost', // 2852
            'quilt', // 2615
            'ocean', // 4018
        ];

        $actual = $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, range('a', 'z'));

        self::assertSame('ocean', $actual);
    }

    /** @test */
    public function shouldReturnHighestScoreFromListWithOnlySomeLetters(): void
    {
        $possibleAnswers = [
            'steer', // 1233
            'civil', // 671
            'bride', // 2185
            'beast', // 2493
            'ghost', // 0
            'quilt', // 671
            'ocean', // 2212
        ];

        $actual = $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, ['a', 'e', 'i', 'b']);

        self::assertSame('beast', $actual);
    }

    /** @test */
    public function shouldReturnFirstAlphabeticalInCaseOfSameScores(): void
    {
        $possibleAnswers = [
            'steer', // 1233
            'civil', // 0
            'bride', // 1233
            'beast', // 1233
            'ghost', // 0
            'quilt', // 0
            'ocean', // 1233
        ];

        $actual = $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, ['e']);

        self::assertSame('beast', $actual);
    }

    /** @test */
    public function shouldThrowOnNoPossibleAnswersGiven(): void
    {
        $possibleAnswers = [];

        self::expectException(NoPossibleAnswers::class);
        $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, ['e']);
    }

    /** @test */
    public function shouldThrowOnNoPossibleScoresGiven(): void
    {
        $possibleAnswers = [
            'beast',
            'ghost',
        ];

        self::expectException(NoPossibleScores::class);
        $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, []);
    }

    /** @test */
    public function shouldThrowOnNoWinnerFound(): void
    {
        $possibleAnswers = [''];

        self::expectException(NoWinnerFound::class);
        $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, ['f']);
    }
}
