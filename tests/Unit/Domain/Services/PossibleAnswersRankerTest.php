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
            'steer', // 160.18
            'civil', // 94.69
            'bride', // 161.78
            'beast', // 175.41
            'ghost', // 129.07
            'quilt', // 121.37
            'ocean', // 193.75
        ];

        $actual = $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, range('a', 'z'));

        self::assertSame('ocean', $actual);
    }

    /** @test */
    public function shouldReturnHighestScoreFromListWithOnlySomeLetters(): void
    {
        $possibleAnswers = [
            'steer', // 56.88
            'civil', // 38.45
            'bride', // 105.89
            'beast', // 110.75
            'ghost', // 0.0
            'quilt', // 38.45
            'ocean', // 100.19
        ];

        $actual = $this->ranker->getHighestRankingPossibleAnswer($possibleAnswers, ['a', 'e', 'i', 'b']);

        self::assertSame('beast', $actual);
    }

    /** @test */
    public function shouldReturnFirstAlphabeticalInCaseOfSameScores(): void
    {
        $possibleAnswers = [
            'steer', // 56.88
            'civil', // 0.0
            'bride', // 56.88
            'beast', // 56.88
            'ghost', // 0.0
            'quilt', // 0.0
            'ocean', // 56.88
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
