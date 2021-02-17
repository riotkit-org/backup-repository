<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

class AccessTokenRevokingForm
{
    public const CURRENT_TOKEN_NAME = 'current-session';

    public string $tokenHash;

    public string $currentSessionTokenHash;
}
