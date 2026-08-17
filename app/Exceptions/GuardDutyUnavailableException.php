<?php

namespace App\Exceptions;

use RuntimeException;

class GuardDutyUnavailableException extends RuntimeException
{
    public static function missing(): self
    {
        return new self('No security guard is currently assigned. Please contact the security desk.');
    }

    public static function missingShift(): self
    {
        return new self('No active guard duty shift was found.');
    }
}
