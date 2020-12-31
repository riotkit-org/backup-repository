<?php declare(strict_types=1);

namespace App\Command\Authentication;

use App\Domain\Roles;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAdminAccountCommand extends Command
{
    public const NAME = 'auth:generate-admin-account';

    protected function configure()
    {
        $this->setName(static::NAME)
            ->setDescription('Generate administrative user account')
            ->addOption('expires', null, InputOption::VALUE_REQUIRED)
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL)
            ->addOption('email', 'm', InputOption::VALUE_REQUIRED)
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED)
            ->addOption('ignore-error-if-already-exists', null, InputOption::VALUE_NONE,
                'Exit with success if token already exists. Does not check strictly permissions and other attributes, just the id.')
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
        $command = $this->getApplication()->find(CreateUserCommand::NAME);

        $opts = [
            'command'    => CreateUserCommand::NAME,
            '--roles'    => Roles::PERMISSION_ADMINISTRATOR,
            '--expires'  => $input->getOption('expires') ?? '+10 years',
            '--id'       => $input->getOption('id') ?? '',
            '--email'    => $input->getOption('email'),
            '--password' => $input->getOption('password')
        ];

        if ($input->getOption('ignore-error-if-already-exists')) {
            $opts['--ignore-error-if-token-exists'] = true;
        }

        return $command->run(new ArrayInput($opts), $output);
    }
}
