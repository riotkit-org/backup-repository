<?php declare(strict_types=1);

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Mink\Exception\ElementNotFoundException;
use PHPUnit\Framework\Assert as Assertions;

define('SERVER_PATH', __DIR__ . '/../../../server');

require_once __DIR__ . '/TechnicalContext.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends TechnicalContext
{
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
     * @Given I login as :user with :password
     *
     * @param string $user
     * @param string $password
     * @throws ElementNotFoundException
     */
    public function iLoginAs(string $user, string $password): void
    {
        $this->visit('/');
        $this->iClickMenuLink('/admin/login');

        $this->fillFieldByCSS('input[placeholder="Email*"]', $user);
        $this->fillFieldByCSS('input[placeholder="Enter your password*"]', $password);
        sleep(1);

        $this->pressButton('Log-in');
        sleep(1);
    }

    /**
     * @When I click menu link leading to :path
     *
     * @param string $path
     * @throws ElementNotFoundException
     */
    public function iClickMenuLink(string $path): void
    {
        $this->findByCSS('a[to="' . $path . '"]')->click();
        $this->iWait();
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

    /**
     * @Given I am authenticated as administrator
     */
    public function iAuthAsAdmin(): void
    {
        $this->iCreateAdminAccountWithEMail('unity@solidarity.local', 'you-cant-break-it');
        $this->iLoginAs('unity@solidarity.local', 'you-cant-break-it');
    }

    /**
     * @When I visit users search page
     * @throws ElementNotFoundException
     */
    public function iVisitUsersSearchPage(): void
    {
        $this->iClickMenuLink('/admin/users');
    }

    /**
     * @Then I should see user :email on the list
     *
     * @param string $email
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeUserOnTheList(string $email): void
    {
        $this->findByCSS('table:contains("' . $email . '")');
    }

    /**
     * @When I logout
     *
     * @throws ElementNotFoundException
     */
    public function iLogout(): void
    {
        $this->iClickMenuLink('/admin/logout');
    }
}
