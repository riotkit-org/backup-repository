<?php declare(strict_types=1);

namespace App\Domain\Common\Service\Bus;

interface CommandHandler
{
    /**
     * @param mixed $input
     *
     * @return mixed
     */
    public function handle($input, string $path);

    /**
     * @return array
     */
    public function getSupportedPaths(): array;
}
