<?php declare(strict_types=1);

use Behat\Mink\Exception\ElementNotFoundException;
use PHPUnit\Framework\Assert as Assertions;

require_once __DIR__ . '/TechnicalContext.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends TechnicalContext
{
    /**
     * State: Keeps last created collection id
     *
     * @var string $lastCreatedCollectionId
     */
    protected string $lastCreatedCollectionId;

    /**
     * State: Keeps last created authorization token (not user account - but user access to existing account)
     *
     * @var string $lastAssignedAuthorizationToken
     */
    protected string $lastAssignedAuthorizationToken;

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
     * @When I visit backups page
     *
     * @throws ElementNotFoundException
     */
    public function iVisitBackupsPage(): void
    {
        $this->iClickMenuLink('/admin/backup/collections');
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

    /**
     * @Given I create a backup with filename=:filename description=:description strategy=:strategy maxBackupsCount=:maxBackupsCount maxOneVersionSize=:maxOneVersionSize maxOverallCollectionSize=:maxOverallCollectionSize
     *
     * @param string $filename
     * @param string $description
     * @param string $strategy
     * @param $maxBackupsCount
     * @param $maxOneVersionSize
     * @param $maxOverallCollectionSize
     */
    public function iCreateABackup(string $filename, string $description, string $strategy, $maxBackupsCount, $maxOneVersionSize, $maxOverallCollectionSize): void
    {
        $this->pressButton('Create a new backup collection');
        $this->fillField('Filename', $filename);
        $this->selectOption('Strategy', $strategy);
        $this->fillField('Description', $description);
        $this->fillField('Max backups count', $maxBackupsCount);
        $this->fillField('Max one version size', $maxOneVersionSize);
        $this->fillField('Max overall collection size', $maxOverallCollectionSize);
        $this->pressButton('Create');
        $this->iWait();

        // get the collection id
        $cellWithId = $this->findByCSS('td[data-column="Id"]');
        $this->lastCreatedCollectionId = $cellWithId->getText();
    }

    /**
     * @Given I copy the authorization token
     */
    public function iCopyTheAuthorizationToken(): void
    {
        $textarea = $this->findByCSS('.generated-token textarea');
        $this->lastAssignedAuthorizationToken = (string) $textarea->getValue();
    }

    /**
     * @When I login using copied JWT
     */
    public function iLoginUsingCopiedJWT(): void
    {
        $this->clickLink('Use JSON Web Token');
        $this->fillField('Encoded JSON Web Token', $this->lastAssignedAuthorizationToken);
        $this->pressButton('Log-in');
        $this->iWait();
    }

    /**
     * @When I visit collections page
     */
    public function iVisitCollectionsPage(): void
    {
        $this->iClickMenuLink('/admin/backup/collections');
    }

    /**
     * @Given I generate a new access key with all permissions
     */
    public function iGenerateANewAccessKeyWithAllPermissions(): void
    {
        $this->iVisitAuthorizationPage();
        $this->pressButton('Grant a new access');
        $this->selectOption('Select how long the token should be valid', '+7 days');
        $this->fillField('description', 'Test token, ' . (string) microtime(true));
        $this->clickLink('Create access token');
        $this->iCopyTheAuthorizationToken();
        $this->iExitModal();
    }

    /**
     * #Type Bahub
     *
     * @When I submit a new backup as part of :backupDefinition definition for collection I just created
     *
     * @param string $backupDefinition
     */
    public function iSubmitANewBackup(string $backupDefinition): void
    {
        $result = $this->execBahubCommand(':backup:make ' . $backupDefinition . ' -rl debug', [
            'API_TOKEN'          => $this->lastAssignedAuthorizationToken,
            'TEST_COLLECTION_ID' => $this->lastCreatedCollectionId,
            'BUILD_DIR'          => BUILD_DIR
        ]);

        Assertions::assertEquals(0, $result['exit_code']);
    }

    /**
     * #Type Bahub
     *
     * @When I issue a backup restore of :version version using :backupDefinition definition for a collection I recently created
     *
     * @param string $backupDefinition
     * @param string $version
     */
    public function iIssueABackupRestore(string $backupDefinition, string $version): void
    {
        $result = $this->execBahubCommand(':backup:restore ' . $backupDefinition . ' --version="' . $version . '" -rl debug', [
            'API_TOKEN'          => $this->lastAssignedAuthorizationToken,
            'TEST_COLLECTION_ID' => $this->lastCreatedCollectionId,
            'BUILD_DIR'          => BUILD_DIR
        ]);

        Assertions::assertEquals(0, $result['exit_code']);
    }

    /**
     * @Given I visit recently created collection page
     */
    public function iVisitRecentlyCreatedCollectionPage(): void
    {
        $this->iVisitBackupsPage();
        $link = $this->findByCSS('small:contains("' . $this->lastCreatedCollectionId . '")');
        $link->click();
    }

    /**
     * @Then I expect that there is :backupNum backup present
     *
     * @param string $backupNum
     *
     * @throws ElementNotFoundException
     */
    public function iExpectThatThereIsBackupPresent(string $backupNum): void
    {
        $version = $this->findByCSS('.versions-table [data-column="Version"]:contains("' . $backupNum . '")');
        Assertions::assertEquals($backupNum, $version->getText());
    }

    /**
     * @Then I expect bahub command finished with success
     */
    public function iExpectBahubCommandFinishedWithSuccess(): void
    {
        Assertions::assertEquals(0, $this->lastBahubCommandExitCode);
    }
}
