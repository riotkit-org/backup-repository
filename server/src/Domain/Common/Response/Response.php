<?php declare(strict_types=1);

namespace App\Domain\Common\Response;

interface Response extends \JsonSerializable
{
    public function isSuccess(): bool;

    public function jsonSerialize(): array;

    public function getHttpCode(): int;
}
