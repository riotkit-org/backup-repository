<?php declare(strict_types=1);

namespace App\Domain\Common\Service\Bus;

interface CommandHandler
{
    /**
     * Handle the action and return a result optionally
     *
     * @param mixed $input
     *
     * @return mixed
     */
    public function handle($input, string $path);

    /**
     * Can command handle given PATH/EVENT with specific INPUT?
     *
     * @param $input
     * @param string $path
     *
     * @return bool
     */
    public function supportsInput($input, string $path): bool;

    /**
     * List of all supported EVENTS/PATHS this command handler can react to
     *
     * @return array
     */
    public function getSupportedPaths(): array;
}
