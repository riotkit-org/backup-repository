<?php declare(strict_types=1);

namespace Tests\Command\Authentication;

use App\Command\Authentication\GenerateAdminTokenCommand;
use App\Command\Authentication\GenerateTokenCommand;
use App\Domain\Authentication\ActionHandler\TokenGenerationHandler;
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
        $application->add(new GenerateTokenCommand(
            $kernel->getContainer()->get(TokenGenerationHandler::class),
            $kernel->getContainer()->get(SecurityContextFactory::class)
        ));

        $command = $client->getContainer()->get(GenerateAdminTokenCommand::class);
        $command->setApplication($application);

        $commandTester = new CommandTester($command);
        $commandTester->execute($opts);

        return $commandTester;
    }

    public function testCreatedAdminTokenIsValidUuid(): void
    {
        $tester = $this->execute([]);

        $this->assertRegExp(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i',
            $tester->getDisplay()
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testCanCreateTokenWithCustomId(): void
    {
        $tester = $this->execute(['--id' => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4']);

        $this->assertSame(
            '1c2c84f2-d488-4ea0-9c88-d25aab139ac4',
            rtrim($tester->getDisplay())
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testCantCreateTokenThatIsNotAValidUuid(): void
    {
        $tester = $this->execute(['--id' => 'industrial-workers-of-the-world']);

        $this->assertStringContainsString('id_expects_to_be_uuidv4_format', $tester->getDisplay());
        $this->assertSame(1, $tester->getStatusCode());
    }

    public function testIsSuccessWhenTokenAlreadyExistsButWantsToSkipThatError(): void
    {
        $this->execute(
            [
                '--id' => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
            ]
        );

        $tester = $this->execute(
            [
                '--id' => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4',
                '--ignore-error-if-token-exists' => true
            ]
        );

        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testReturnsFailureWhenTokenIdAlreadyExistsAndNotUsedSwitch(): void
    {
        $this->execute(
            [
                '--id' => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
            ]
        );

        $tester = $this->execute(
            [
                '--id' => '1c2c84f2-d488-4ea0-9c88-d25aab139ac4'
                // --ignore-error-if-token-exists is not used there
            ]
        );

        $this->assertSame(1, $tester->getStatusCode());
    }
}
