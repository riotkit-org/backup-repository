<?php declare(strict_types=1);

namespace App\Domain\Common\Service\Bus;

class DomainBus
{
    /**
     * @var CommandHandler[]
     */
    private $commands;

    /**
     * @param CommandHandler[] $commands
     */
    public function __construct(array $commands)
    {
        foreach ($commands as $command) {
            if (!$command instanceof CommandHandler) {
                throw new \InvalidArgumentException('Command need to implement CommandHandler interface');
            }

            foreach ($command->getSupportedPaths() as $path) {
                if (isset($this->commands[$path])) {
                    throw new \LogicException('There is already a command registered at "' . $path . '"');
                }

                $this->commands[$path] = $command;
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

        $command = $this->commands[$path];
        return $command->handle($input);
    }
}
