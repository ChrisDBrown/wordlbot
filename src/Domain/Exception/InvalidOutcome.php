<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use InvalidArgumentException;

abstract class InvalidOutcome extends InvalidArgumentException
{
}
