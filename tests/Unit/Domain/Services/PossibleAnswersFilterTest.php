<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Services;

use App\Domain\Exception\FilterReturnsEmpty;
use App\Domain\Services\PossibleAnswersFilter;
use App\Domain\ValueObject\Result;
use App\Domain\ValueObject\ResultHistory;
use PHPUnit\Framework\TestCase;

/** @covers PossibleAnswersFilter */
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
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('zzzst', 'aaacc')); // ...st

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
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('foost', 'aaacc')); // miss f, o

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
    public function shouldNotFilterAnswersWhereLetterBothMatchesAndMisses(): void
    {
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('natal', 'pcpaa'));

        $possibleAnswers = ['tangy'];

        $expected = ['tangy'];

        $actual = $this->filter->getValidAnswersForHistory($possibleAnswers, $resultHistory);

        self::assertSame($expected, $actual);
    }

    /** @test */
    public function shouldFilterAnswersNotContainingKnownMatches(): void
    {
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('forge', 'ppaaa')); // match f, o

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

    /** @test */
    public function shouldThrowIfNoAnswersRemain(): void
    {
        $resultHistory = new ResultHistory();
        $resultHistory->addResult(new Result('forge', 'ccccc'));

        $possibleAnswers = [
            'feast',
            'toast',
            'beast',
            'foist',
        ];

        self::expectException(FilterReturnsEmpty::class);
        $this->filter->getValidAnswersForHistory($possibleAnswers, $resultHistory);
    }
}
