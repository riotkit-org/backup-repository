<?php declare(strict_types=1);

use Behat\Mink\Exception\ElementNotFoundException;
use PHPUnit\Framework\Assert as Assertions;

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
        $this->pressButton('Log-in');

        $this->iWait();
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
        $this->iWait();
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
     * @When I visit authorization page
     *
     * @throws ElementNotFoundException
     */
    public function iVisitAuthorizationPage(): void
    {
        $this->iClickMenuLink('/admin/security/access-tokens');
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
     * @When I revoke the token :description
     *
     * @param string $description
     * @throws ElementNotFoundException
     */
    public function iRevokeAccessToken(string $description): void
    {
        $this->findByCSS('input[data-value="' . $description . '"]')
            ->getParent() /* td */
            ->getParent() /* tr */
            ->find('css', 'button[data-field="Revoke"]')->click();

        $this->iWait();
    }

    /**
     * @Then I expect the Revoke button will be disabled for :description
     *
     * @param string $description
     * @throws ElementNotFoundException
     */
    public function iExpectRevokeButtonShouldBeDisabled(string $description): void
    {
        $this->findByCSS('input[data-value="' . $description . '"]')
            ->getParent() /* td */
            ->getParent() /* tr */
            ->find('css', 'button[data-field="Revoke"]')->getAttribute('disabled') === 'disabled';
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

    /**
     * @Given I generate keys for existing backup configuration entry :backupDefinition
     *
     * @param string $backupDefinition
     */
    public function iGenerateKeysForExistingBackupConfigurationEntry(string $backupDefinition): void
    {
        $this->execBahubCommand(':crypto:generate-keys ' . $backupDefinition . ' -rl debug', [
            // values are example - the keys generation and listing does not use them
            'API_TOKEN'          => '1111-2222-3333',
            'TEST_COLLECTION_ID' => '1111-2222-3333',
            'BUILD_DIR'          => BUILD_DIR
        ]);
    }

    /**
     * @Then I should have gpg key described as :descriptionPart
     *
     * @param string $descriptionPart
     */
    public function iShouldHaveGpgKeyDescribedAs(string $descriptionPart): void
    {
        $result = $this->execBahubCommand(':crypto:list-keys', [
            // values are example - the keys generation and listing does not use them
            'API_TOKEN'          => '1111-2222-3333',
            'TEST_COLLECTION_ID' => '1111-2222-3333',
            'BUILD_DIR'          => BUILD_DIR
        ]);

        Assertions::assertStringContainsStringIgnoringCase($descriptionPart, $result['out']);
    }

    /**
     * @Then I should see error output from bahub containing :text
     *
     * @param string $text
     */
    public function iShouldSeeErrorOutputFromBahubContaining(string $text): void
    {
        Assertions::assertStringContainsStringIgnoringCase($text, $this->lastBahubCommandResponse);
    }
}
