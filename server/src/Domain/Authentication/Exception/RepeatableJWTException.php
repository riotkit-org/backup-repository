<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

class RepeatableJWTException extends AuthenticationException
{
    public static function fromJWTAlreadyRecorded(): RepeatableJWTException
    {
        return new static('JWT already recorded');
    }
}
