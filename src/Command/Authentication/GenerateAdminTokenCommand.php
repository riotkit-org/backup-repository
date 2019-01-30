<?php declare(strict_types=1);

namespace App\Command\Authentication;

use App\Domain\Roles;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAdminTokenCommand extends Command
{
    public const NAME = 'auth:generate-admin-token';

    protected function configure()
    {
        $this->setName(static::NAME)
            ->setDescription('Generate administrative token')
            ->addOption('expires', null, InputOption::VALUE_REQUIRED)
            ->setHelp('With admin token you have unlimited access to the application');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating admin token...');
        $command = $this->getApplication()->find(GenerateTokenCommand::NAME);

        return $command->run(
            new ArrayInput([
                'command' => GenerateTokenCommand::NAME,
                '--roles'   => Roles::ROLE_ADMINISTRATOR,
                '--expires' => $input->getOption('expires') ?? '+10 years'
            ]),
            $output
        );
    }
}