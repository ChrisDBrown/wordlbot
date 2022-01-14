<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Services;

use App\Domain\Services\Evaluator;
use Generator;
use PHPUnit\Framework\TestCase;

/** @covers Evaluator */
final class EvaluatorTest extends TestCase
{
    private Evaluator $evaluator;

    public function setUp(): void
    {
        $this->evaluator = new Evaluator();

        parent::setUp();
    }

    /**
     * @test
     * @dataProvider guesses
     */
    public function shouldEvaluateGuesses(string $guess, string $answer, string $expected): void
    {
        $actual = $this->evaluator->evaluate($guess, $answer);

        self::assertSame($expected, $actual);
    }

    /** @return Generator<string, array{0: string, 1: string, 2: string}, void, void> */
    public function guesses(): Generator
    {
        yield 'Only highlight first match of a repeated letter, a' => [
            'awake',
            'tangy',
            'paaaa',
        ];

        yield 'Highlight both matches if a letter appears twice, a' => [
            'awake',
            'banal',
            'papaa',
        ];

        yield 'Highlight a full miss' => [
            'gorge',
            'banal',
            'aaaaa',
        ];

        yield 'Highlight an exact match' => [
            'siege',
            'siege',
            'ccccc',
        ];

        yield 'Highlight a partial match, 1' => [
            'taint',
            'tangy',
            'ccapa',
        ];

        yield 'Highlight a partial match, 2' => [
            'vapor',
            'favor',
            'pcacc',
        ];

        yield 'Highlight a partial match, 3' => [
            'beast',
            'abbey',
            'pppaa',
        ];
    }
}
