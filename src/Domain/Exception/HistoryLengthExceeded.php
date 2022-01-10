<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use OutOfBoundsException;

final class HistoryLengthExceeded extends OutOfBoundsException
{
}
