<?php declare(strict_types=1);

namespace E2E\features\bootstrap;

use Behat\Mink\Exception\ElementNotFoundException;
use DateInterval;
use PHPUnit\Framework\Assert as Assertions;

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
     * @When I check server health from the commandline
     */
    public function iCheckServerHealthFromCommandline(): void
    {
        $this->execServerCommand('health:check');
    }

    /**
     * @When I call health endpoint giving :code as access code
     *
     * @param string $code
     */
    public function iCallHealthCheckEndpoint(string $code): void
    {
        $this->visit('/health?code=' . $code);
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
            'TEST_COLLECTION_ID' => '1111-2222-3333'
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
        Assertions::assertStringContainsStringIgnoringCase($text, $this->environmentController->getLastBahubCommandResponse());
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
     *
     * @throws ElementNotFoundException
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

        $this->iCopyCollectionIdFromSearch();
    }

    /**
     * @Then I copy id of a just created backup collection
     *
     * @throws ElementNotFoundException
     */
    public function iCopyCollectionIdFromSearch(): void
    {
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
     * @When /^(I|I again) submit a new backup as part of "([^"]*)" definition for collection I recently created$/
     *
     * @param string $a
     * @param string $backupDefinition
     */
    public function iSubmitANewBackup(string $a, string $backupDefinition): void
    {
        $this->execBahubCommand('@ -rl debug :backup:make ' . $backupDefinition . ' ', [
            'API_TOKEN'          => $this->lastAssignedAuthorizationToken,
            'TEST_COLLECTION_ID' => $this->lastCreatedCollectionId,
        ]);

        // no assertion: error may be expected there
    }

    /**
     * #Type Bahub
     *
     * @When I import SQL file :file into PostgreSQL test instance connecting to :db database
     *
     * @param string $file
     * @param string $db
     */
    public function iImportSQLDumpIntoPostgreSQL(string $file, string $db): void
    {
        passthru('docker cp ' . __DIR__ . '/../../' . $file . ' s3pb_db_postgres_1:/tempfile.sql');
        passthru('docker exec -it --user postgres s3pb_db_postgres_1 /bin/sh -c "cat /tempfile.sql | psql -U bakunin ' . $db . '"');
    }

    /**
     * @Then I expect :expects when I execute :sql on PostgreSQL test instance using :db database
     *
     * @param string $sql
     * @param string $db
     * @param string $expects
     */
    public function iExecPostgreSQLQueryAndExpect(string $expects, string $sql, string $db): void
    {
        exec('docker exec -it --user postgres s3pb_db_postgres_1 /bin/sh -c "echo \'' . $sql . '\' | psql -t -U bakunin ' . $db . '"', $output);
        $output = trim(implode("\n", $output));

        Assertions::assertStringContainsString($expects, $output);
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
            'TEST_COLLECTION_ID' => $this->lastCreatedCollectionId
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
     * @Then I expect that there are :backupNum backups present
     *
     * @param string $backupsNum
     *
     * @throws ElementNotFoundException
     */
    public function iExpectThatThereIsBackupPresent(string $backupsNum): void
    {
        foreach (explode(',', $backupsNum) as $backupNum) {
            $backupNum = trim($backupNum);
            $version = $this->findByCSS('.versions-table [data-column="Version"]:contains("' . $backupNum . '")');
            Assertions::assertEquals($backupNum, $version->getText());
        }
    }

    /**
     * @Then I expect that :backupNum backups are not present
     *
     * @param string $backupsNum
     */
    public function iExpectThatThereIsNoGivenBackupPresent(string $backupsNum): void
    {
        foreach (explode(',', $backupsNum) as $backupNum) {
            $backupNum = trim($backupNum);

            try {
                $this->findByCSS('.versions-table [data-column="Version"]:contains("' . $backupNum . '")');
                Assertions::assertEquals(false, true, 'Expected that version ' . $backupNum . ' will not be present');

            } catch (ElementNotFoundException $exception) { }
        }
    }

    /**
     * @Then I expect bahub command finished with success
     */
    public function iExpectBahubCommandFinishedWithSuccess(): void
    {
        Assertions::assertEquals(0, $this->environmentController->getLastBahubCommandExitCode());
    }

    /**
     * @Then I expect last bahub command output contains :text
     *
     * @param string $text
     */
    public function iExpectBahubCommandOutputContains(string $text): void
    {
        Assertions::assertStringContainsStringIgnoringCase($text, $this->environmentController->getLastBahubCommandResponse());
    }

    /**
     * @Given I start creating user account :email identified by :password for organization :organization
     *
     * @param string $email
     * @param string $password
     * @param string $organization
     *
     * @throws ElementNotFoundException
     */
    public function iStartCreatingUserAccountIdentifiedByForOrganization(string $email, string $password, string $organization): void
    {
        $this->iVisitUsersSearchPage();
        $this->pressButton('Add user');
        $this->iWait();
        $this->fillField('Email', $email);
        $this->iWait();
        $this->fillField('Organization', $organization);
        $this->iWait();
        $this->fillField('New password', $password);
        $this->iWait();
        $this->fillField('Repeat password', $password);
        $this->iWait();
    }

    /**
     * @When I pick date :date from :label
     */
    public function iPickDateFromDatepicker(string $date, string $label): void
    {
        $templates = [
            '{yesterday}' => (new \DateTime())->add(DateInterval::createFromDateString('yesterday'))->format('Y/m/d'),
            '{tommorow}'  => (new \DateTime())->add(DateInterval::createFromDateString('+1 day'))->format('Y/m/d'),
        ];

        foreach ($templates as $templateKey => $templateValue) {
            $date = str_replace($templateKey, $templateValue, $date);
        }

        $element = $this->findByCSS($this->createInputSelector($label));
        $element->click();

        $this->fillField($label, $date);
        $this->iWait();
    }

    /**
     * @Given I submit creation of user account
     */
    public function iSubmitCreationOfUserAccount(): void
    {
        $this->pressButton('Add new user');
    }

    /**
     * @Given I start creating collection :collection described :description and :strategy strategy
     *
     * @param string $collection
     * @param string $description
     * @param string $strategy
     *
     * @throws ElementNotFoundException
     */
    public function iStartCreatingCollection(string $collection, string $description, string $strategy): void
    {
        $this->iVisitBackupsPage();
        $this->pressButton('Create a new backup collection');
        $this->fillField('Filename', $collection);
        $this->fillField('Description', $description);
        $this->selectOption('Strategy', $strategy);
    }

    /**
     * @Given I start adding new permissions to collection for user :email
     *
     * @param string $email
     *
     * @throws ElementNotFoundException
     */
    public function iStartAddingNewPermissionsToCollectionForUser(string $email): void
    {
        $this->findByCSS('[data-field="Grant a new access"]')->click();
        $this->selectOption('-- Please select a user', $email);
    }

    /**
     * @Then I finalize adding new permissions to collection
     *
     * @throws ElementNotFoundException
     */
    public function iFinalizeAddingNewPermissionsToCollection(): void
    {
        $this->findByCSS('i[data-field="Submit new access to this collection"]')->click();
    }
}
