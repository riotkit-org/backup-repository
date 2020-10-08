<?php declare(strict_types=1);

namespace App\Command\Authentication;

use App\Domain\Authentication\ActionHandler\UserCreationHandler;
use App\Domain\Authentication\Exception\ValidationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AuthForm;
use App\Domain\Authentication\Form\RestrictionsForm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    public const NAME = 'auth:create-token';

    private UserCreationHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(UserCreationHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('auth:create-token')
            ->setDescription('Creates an authentication token')
            ->addOption('roles', null, InputOption::VALUE_REQUIRED)
            ->addOption('tags', null, InputOption::VALUE_REQUIRED)
            ->addOption('max-file-size', null, InputOption::VALUE_REQUIRED)
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL)
            ->addOption('email', 'm', InputOption::VALUE_REQUIRED)
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED)
            ->addOption('expires', null, InputOption::VALUE_REQUIRED, 'Example: 2020-05-01 or +10 years')
            ->addOption('ignore-error-if-user-exists', null, InputOption::VALUE_NONE,
                'Exit with success if token already exists. Does not check strictly permissions and other attributes, just the id.')
            ->setHelp('Allows to generate a token you can use later to authenticate in application for a specific thing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $form = new AuthForm();
        $form->data = new RestrictionsForm();
        $form->data->tags               = $this->getMultipleValueOption($input, 'tags');
        $form->data->maxAllowedFileSize = (int) $input->getOption('max-file-size');
        $form->expires                  = $input->getOption('expires');
        $form->roles                    = $this->getMultipleValueOption($input, 'roles');
        $form->id                       = $input->getOption('id');
        $form->email                    = $input->getOption('email');
        $form->password                 = $input->getOption('password');

        $this->debug('Form:', $output);

        foreach ($form->data->tags as $tag) {
            $this->debug(' [Tag] -> ' . $tag, $output);
        }

        foreach ($form->roles as $role) {
            $this->debug(' [Role] -> ' . $role, $output);
        }

        try {
            $response = $this->handler->handle(
                $form,
                $this->authFactory->createShellContext()
            );
        } catch (ValidationException $validationException) {
            if ($input->getOption('ignore-error-if-user-exists')
                && $validationException->hasOnlyError('id_already_exists_please_select_other_one')) {

                $output->writeln($form->id);
                return 0;
            }

            $this->debug("", $output);
            $output->writeln('Validation error:');

            foreach ($validationException->getFields() as $field => $errors) {
                $output->writeln(' Field "' . $field . '":');

                foreach ($errors as $error) {
                    $output->writeln(' - ' . $error);
                }
            }

            return 1;
        }

        $this->debug("\nResponse:", $output);
        $this->debug('========================', $output);
        $this->debug(json_encode($response, JSON_PRETTY_PRINT), $output);

        if (!$output->isVerbose()) {
            $output->writeln($response->getUserId() ?? '');
        }

        return 0;
    }

    private function debug(string $message, OutputInterface $output): void
    {
        if (!$output->isVerbose()) {
            return;
        }

        $output->writeln($message);
    }

    private function getMultipleValueOption(InputInterface $input, string $optionName): array
    {
        $values = explode(',', $input->getOption($optionName) ?: '');
        $values = array_filter($values);

        return $values;
    }
}
