<?php declare(strict_types=1);

namespace App\Domain\Common\Service\Bus;

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
     * @param string $path
     * @param array $input
     *
     * @return mixed
     */
    public function call(string $path, array $input)
    {
        if (!isset($this->commands[$path])) {
            throw new \InvalidArgumentException('"' . $path . '" is not a recognized domain path');
        }

        if (\count($this->commands[$path]) > 1) {
            throw new \LogicException(
                'Cannot make a call() with return, when there are registered more than one handler');
        }

        /**
         * @var CommandHandler $command
         */
        $command = $this->commands[$path][0];
        return $command->handle($input, $path);
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
            $command->handle($input, $path);
        }
    }
}
