<?php declare(strict_types=1);

namespace App\Domain\Common\Service\Bus;

use App\Domain\Common\Exception\BusException;

/**
 * Domain connector. Allows to subscribe on one side, and notify all subscribers on the other side.
 * Works like EventSubscriber in Symfony, but on the Domain surface.
 */
class DomainBus
{
    /**
     * @var array[]
     */
    private $commands;

    /**
     * @param array[] $commands
     */
    public function setCommands(array $commands): void
    {
        if (!empty($this->commands)) {
            throw new \LogicException('setCommands() is immutable');
        }

        foreach ($commands as $command) {
            if (!$command instanceof CommandHandler) {
                throw new \InvalidArgumentException('Command need to implement CommandHandler interface');
            }

            foreach ($command->getSupportedPaths() as $path) {
                if (isset($this->commands[$path])) {
                    throw new \LogicException('There is already a command registered at "' . $path . '"');
                }

                $this->commands[$path][] = $command;
            }
        }
    }

    /**
     * Call a command, expect only one command attached to given call
     *
     * @param string $path
     * @param array $input
     *
     * @return mixed
     *
     * @throws BusException
     */
    public function call(string $path, array $input)
    {
        $this->assertAnyCommandWasRegistered($path);

        if (\count($this->commands[$path]) > 1) {
            throw new BusException(
                'Cannot make a call() with return, when there are registered more than one handler',
                BusException::CALL_ON_NON_SINGLE_COMMAND
            );
        }

        /**
         * @var CommandHandler $command
         */
        $command = $this->commands[$path][0];
        return $command->handle($input, $path);
    }

    /**
     * Call all commands, and stop at first that will not return NULL
     *
     * @param string $path
     * @param array $input
     *
     * @return mixed|null
     *
     * @throws BusException
     */
    public function callForFirstMatching(string $path, array $input)
    {
        $this->assertAnyCommandWasRegistered($path);

        foreach ($this->commands[$path] as $command) {
            if (!$command->supportsInput($input, $path)) {
                continue;
            }

            /**
             * @var CommandHandler $command
             */
            return $command->handle($input, $path);
        }

        throw new BusException(
            'No command responded with "supportsInput() === true" for path "' . $path . '" and given input',
            BusException::NO_COMMAND_RESPONDED_CORRECTLY
        );
    }

    private function assertAnyCommandWasRegistered(string $path): void
    {
        if (!isset($this->commands[$path])) {
            throw new BusException(
                '"' . $path . '" is not a recognized domain path in bus',
                BusException::NO_COMMAND_REGISTERED
            );
        }
    }

    public function broadcast(string $path, array $input): void
    {
        if (!isset($this->commands[$path]) || empty($this->commands[$path])) {
            return;
        }

        /**
         * @var CommandHandler $command
         */
        foreach ($this->commands[$path] as $command) {
            if (!$command->supportsInput($input, $path)) {
                continue;
            }

            $command->handle($input, $path);
        }
    }
}
