<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObject\ResultHistory;

use function array_key_exists;
use function array_keys;
use function array_merge;

final class EvaluationOutcomes
{
    /** @var array<int, EvaluationOutcome> */
    private array $passes = [];
    /** @var array<class-string, array<int, EvaluationOutcome>> */
    private array $failures = [];

    public function addPass(string $answer, ResultHistory $resultHistory): void
    {
        $this->passes[] = new EvaluationOutcome($answer, $resultHistory);
    }

    /** @param class-string $errorClass */
    public function addFailure(string $errorClass, string $answer, ResultHistory $resultHistory): void
    {
        if (! array_key_exists($errorClass, $this->failures)) {
            $this->failures[$errorClass] = [];
        }

        $this->failures[$errorClass][] = new EvaluationOutcome($answer, $resultHistory);
    }

    /** @return array<int, EvaluationOutcome> */
    public function getPasses(): array
    {
        return $this->passes;
    }

    /** @return array<int, EvaluationOutcome> */
    public function getAllFailures(): array
    {
        $allFailures = [];
        foreach ($this->failures as $class => $failures) {
            $allFailures = array_merge($allFailures, $failures);
        }

        return $allFailures;
    }

    /** @return array<int, class-string> */
    public function getErrorClasses(): array
    {
        return array_keys($this->failures);
    }

    /**
     * @param class-string $errorClass
     *
     * @return array<int, EvaluationOutcome>
     */
    public function getFailuresOfErrorClass(string $errorClass): array
    {
        return $this->failures[$errorClass] ?? [];
    }
}
