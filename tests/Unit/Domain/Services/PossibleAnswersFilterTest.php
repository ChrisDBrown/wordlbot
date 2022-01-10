<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Services;

use App\Domain\Services\PossibleAnswersFilter;
use App\Domain\ValueObject\Interface\ResultHistory;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class PossibleAnswersFilterTest extends TestCase
{
    private PossibleAnswersFilter $filter;

    public function setUp(): void
    {
        $this->filter = new PossibleAnswersFilter();

        parent::setUp();
    }

    /** @test */
    public function shouldFilterAnswersNotMatchingKnownPositions(): void
    {
        $resultHistory = $this->buildResultHistoryMock(knownPositions: '...st');

        $possibleAnswers = [
            'kevin',
            'feast',
            'billy',
            'toast',
            'spies',
            'beast',
            'quint',
            'foist',
        ];

        $expected = [
            'feast',
            'toast',
            'beast',
            'foist',
        ];

        $actual = $this->filter->getValidAnswersForHistory($possibleAnswers, $resultHistory);

        self::assertSame($expected, $actual);
    }

    /** @test */
    public function shouldFilterAnswersContainingKnownMisses(): void
    {
        $resultHistory = $this->buildResultHistoryMock(
            misses: ['f', 'o']
        );

        $possibleAnswers = [
            'feast',
            'toast',
            'beast',
            'foist',
        ];

        $expected = ['beast'];

        $actual = $this->filter->getValidAnswersForHistory($possibleAnswers, $resultHistory);

        self::assertSame($expected, $actual);
    }

    /** @test */
    public function shouldFilterAnswersNotContainingKnownMatches(): void
    {
        $resultHistory = $this->buildResultHistoryMock(
            matches: ['f', 'o']
        );

        $possibleAnswers = [
            'feast',
            'toast',
            'beast',
            'foist',
        ];

        $expected = ['foist'];

        $actual = $this->filter->getValidAnswersForHistory($possibleAnswers, $resultHistory);

        self::assertSame($expected, $actual);
    }

    /**
     * @param array<int, string> $misses
     * @param array<int, string> $matches
     */
    private function buildResultHistoryMock(string $knownPositions = '.....', array $misses = [], array $matches = []): ResultHistory
    {
        $resultHistory = m::mock(ResultHistory::class);
        $resultHistory->shouldReceive([
            'getKnownLetterPositions' => $knownPositions,
            'getKnownLetterMisses' => $misses,
            'getKnownLetterMatches' => $matches,
        ]);

        return $resultHistory;
    }
}
