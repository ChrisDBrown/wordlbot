<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Services;

use App\Domain\Services\Guesser;
use App\Domain\Services\Interface\PossibleAnswersFilter;
use App\Domain\Services\Interface\PossibleAnswersProvider;
use App\Domain\Services\Interface\PossibleAnswersRanker;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use Mockery as M;
use PHPUnit\Framework\TestCase;

/** @covers Guesser */
final class GuesserTest extends TestCase
{
    private M\LegacyMockInterface $provider;
    private M\LegacyMockInterface $filter;
    private M\LegacyMockInterface $ranker;

    private Guesser $guesser;

    public function setUp(): void
    {
        $this->provider = M::mock(PossibleAnswersProvider::class);
        $this->filter   = M::mock(PossibleAnswersFilter::class);
        $this->ranker   = M::mock(PossibleAnswersRanker::class);

        $this->guesser = new Guesser(
            $this->provider,
            $this->filter,
            $this->ranker
        );

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
        $resultHistory->addResult(new Result('beast', 'aaaaa'));

        $actual = $this->guesser->guess($resultHistory);

        self::assertSame('round', $actual);
    }

    /** @test */
    public function shouldReturnDefaultGuessOnThirdAttemptIfNoMatches(): void
    {
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('beast', 'aaaaa'));
        $resultHistory->addResult(new Result('round', 'aaaaa'));

        $actual = $this->guesser->guess($resultHistory);

        self::assertSame('lymph', $actual);
    }

    /** @test */
    public function shouldReturnGuess(): void
    {
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('beast', 'aacpa'));

        $possibleAnswers = [
            'beast',
            'shard',
            'boast',
            'tears',
            'crash',
        ];

        $this->provider
            ->shouldReceive('getAllPossibleAnswers')
            ->andReturn($possibleAnswers);
        $this->filter
            ->shouldReceive('getValidAnswersForHistory')
            ->with($possibleAnswers, $resultHistory)
            ->andReturn([
                'shard',
                'tears',
            ]);
        $this->ranker
            ->shouldReceive('getHighestRankingPossibleAnswer')
            ->with(['shard', 'tears'], $resultHistory->getUnguessedLetters())
            ->andReturn('tears');

        $actual = $this->guesser->guess($resultHistory);

        self::assertSame('tears', $actual);
    }
}
