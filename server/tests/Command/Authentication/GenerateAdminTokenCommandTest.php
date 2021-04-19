<?php declare(strict_types=1);

namespace Tests\Command\Authentication;

use App\Command\Authentication\CreateAdminAccountCommand;
use App\Command\Authentication\CreateUserCommand;
use App\Domain\Authentication\ActionHandler\UserCreationHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\FunctionalTestCase;
use Tests\RestoreDbBetweenTestsTrait;

class GenerateAdminTokenCommandTest extends FunctionalTestCase
{
    use RestoreDbBetweenTestsTrait;

    public function execute(array $opts): CommandTester
    {
        $client = self::createClient();
        $kernel = $client->getKernel();
        $application = new Application($kernel);
        $application->add(new CreateUserCommand(
            $kernel->getContainer()->get(UserCreationHandler::class),
            $kernel->getContainer()->get(SecurityContextFactory::class)
        ));

        $command = $client->getContainer()->get(CreateAdminAccountCommand::class);
        $command->setApplication($application);

        $commandTester = new CommandTester($command);
        $commandTester->execute($opts);

        return $commandTester;
    }

    public function testCanCreateTokenWithCustomId(): void
    {
        $tester = $this->execute([
            '--id' => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4',
            '--email'    => 'anarchist-black-cross@example.org',
            '--password' => 'workers-united-cannot-be-defeated'
        ]);

        $this->assertSame(
            '1c2c84f2-d488-4ea0-9c88-d25aab139ac4',
            rtrim($tester->getDisplay())
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testCantCreateTokenThatIsNotAValidUuid(): void
    {
        $tester = $this->execute([
            '--id' => 'industrial-workers-of-the-world',
            '--email'    => 'anarchist-black-cross@example.org',
            '--password' => 'workers-united-cannot-be-defeated'
        ]);

        $this->assertStringContainsString('User ID format invalid, should be a uuidv4 format', $tester->getDisplay());
        $this->assertSame(1, $tester->getStatusCode());
    }

    public function testIsSuccessWhenTokenAlreadyExistsButWantsToSkipThatError(): void
    {
        $this->execute(
            [
                '--email'    => 'anarchist-black-cross@example.org',
                '--password' => 'workers-united-cannot-be-defeated',
            ]
        );

        $tester = $this->execute(
            [
                '--email'    => 'anarchist-black-cross@example.org',
                '--password' => 'workers-united-cannot-be-defeated',
                '--ignore-error-if-already-exists' => true
            ]
        );

        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testReturnsFailureWhenTokenIdAlreadyExistsAndNotUsedSwitch(): void
    {
        $this->execute(
            [
                '--email'    => 'anarchist-black-cross@example.org',
                '--password' => 'workers-united-cannot-be-defeated',
            ]
        );

        $tester = $this->execute(
            [
                '--email'    => 'anarchist-black-cross@example.org',
                '--password' => 'workers-united-cannot-be-defeated',
            ]
        );

        $this->assertSame(1, $tester->getStatusCode());
    }
}
