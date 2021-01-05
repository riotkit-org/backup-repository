<?php declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit\Framework\Assert as Assertions;

define('SERVER_PATH', __DIR__ . '/../../../server');

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    private string $lastShellCommandResponse = '';
    private int $lastShellCommandExitCode = 1;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    protected function execServerCommand(string $command): array
    {
        exec('cd ' . SERVER_PATH . ' && ./bin/console ' . $command, $output, $returnCode);

        $this->lastShellCommandResponse = implode("\n", $output);
        $this->lastShellCommandExitCode = $returnCode;

        return ['out' => $output, 'exit_code' => $returnCode];
    }

    protected function fillFieldByCSS(string $selector, string $value)
    {
        $input = $this->getSession()->getPage()->find('css', $selector);
        $input->click();

        $input->setValue($value);
        $input->blur();
        $input->click();
    }

    //
    // Authentication
    //

    /**
     * @Given I create admin account with e-mail :email and :password password
     *
     * @param string $email
     * @param string $password
     */
    public function iCreateAdminAccountWithEMail(string $email, string $password): void
    {
        $out = $this->execServerCommand(
            'auth:create-admin-account --email="' . $email . '" --password="' . $password . '" ' .
            '--ignore-error-if-already-exists'
        );

        Assertions::assertSame(0, $out['exit_code']);
    }

    /**
     * @When I create admin account from shell command with :options advanced options
     *
     * @param string $commandline
     */
    public function iCreateAdminAccountAdvanced(string $commandline): void
    {
        $this->execServerCommand(
            'auth:create-admin-account ' . $commandline
        );
    }

    /**
     * @Then I expect the server command contains :partial in output
     *
     * @param string $partial
     */
    public function iAssertShellCommandOutputContains(string $partial): void
    {
        Assertions::assertStringContainsString($partial, $this->lastShellCommandResponse);
    }

    /**
     * @Then I expect the server command exited with :exitCode exit code
     *
     * @param int $exitCode
     */
    public function iAssertShellCommandExitedWith(int $exitCode): void
    {
        Assertions::assertEquals($exitCode, $this->lastShellCommandExitCode);
    }

    /**
     * @Given I login as :user with :password
     *
     * @param string $user
     * @param string $password
     */
    public function iLoginAs(string $user, string $password)
    {
        $this->visit('/');
        $this->getSession()->getPage()->find('css', 'a[to="/admin/login"]')->click();

        $this->fillFieldByCSS('input[placeholder="Email*"]', $user);
        $this->fillFieldByCSS('input[placeholder="Enter your password*"]', $password);
        sleep(1);

        $this->pressButton('Log-in');
        sleep(1);
    }

    /**
     * @Then I expect to be logged in
     */
    public function expectToBeLoggedIn(): void
    {
        $this->assertUrlRegExp('/\/\#\/admin\/Overview/');
    }

    /**
     * @Then I expect that the footer containing application version is visible
     */
    public function expectToSeeApplicationFooter(): void
    {
        $footer = $this->getSession()->getPage()->find('css', 'div[class="footer-menu"]');

        Assertions::assertMatchesRegularExpression('/Backup Repository ([0-9\.\-a-z]+) running on ([a-z]+)/', $footer ? $footer->getText() : '');
        Assertions::assertTrue($footer && $footer->isVisible());
    }
}
