<?php declare(strict_types=1);

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\Assert as Assertions;

define('SERVER_PATH', __DIR__ . '/../../../server');
define('BAHUB_PATH', __DIR__ . '/../../../bahub');
define('BACKEND_URL', 'http://localhost:8000');
define('BUILD_DIR', __DIR__ . '/../../build');

class TechnicalContext extends MinkContext
{
    protected string $lastShellCommandResponse = '';
    protected int $lastShellCommandExitCode = 1;

    protected string $lastBahubCommandResponse = '';
    protected int $lastBahubCommandExitCode = 1;

    /**
     * Before each scenario clear the browser session - logout user
     *
     * @param AfterScenarioScope $event
     *
     * @AfterScenario
     */
    public function resetCurrentPage(AfterScenarioScope $event)
    {
        try {
            $script = 'sessionStorage.clear(); localStorage.clear();';
            $this->getSession()->executeScript($script);
        } catch (Throwable $e) {
        }
    }

    /**
     * Clear temporary build directory after each scenario
     *
     * @param BeforeScenarioScope $event
     *
     * @BeforeScenario
     */
    public function clearTemporaryDirectory(BeforeScenarioScope $event)
    {
        shell_exec('rm -rf ' . BUILD_DIR . '/*');
        shell_exec('touch ' . BUILD_DIR . '/.gitkeep');
    }

    /**
     * @AfterScenario
     *
     * @param AfterScenarioScope $event
     *
     * @throws GuzzleException
     */
    public function restoreDBFromBackup(AfterScenarioScope $event): void
    {
        $http = new Client();
        $http->get(BACKEND_URL . '/db/restore');
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $event
     *
     * @throws GuzzleException
     */
    public function makeDBBackup(BeforeScenarioScope $event): void
    {
        $http = new Client();
        $http->get(BACKEND_URL . '/db/backup');
    }

    //
    // Server's shell interface
    //

    protected function execServerCommand(string $command): array
    {
        exec('cd ' . SERVER_PATH . ' && ./bin/console ' . $command, $output, $returnCode);

        $this->lastShellCommandResponse = implode("\n", $output);
        $this->lastShellCommandExitCode = $returnCode;

        return ['out' => $output, 'exit_code' => $returnCode];
    }

    public function execBahubCommand(string $command, array $env = []): array
    {
        $envsAsString = '';

        foreach ($env as $name => $value) {
            $envsAsString .= ' && export ' . $name . '=' . escapeshellarg($value) . ' ';
        }

        $fullCommand = 'cd ' . BAHUB_PATH . ' ' .
            '&& source .venv/bin/activate ' .
            $envsAsString .
            '&& export CONFIG=' . BAHUB_PATH . '/bahub.conf.yaml ' .
            '&& python3 -m bahub ' . $command . ' 2>&1';

        exec($fullCommand, $output, $returnCode);

        $this->lastBahubCommandResponse = implode("\n", $output);
        $this->lastBahubCommandExitCode = $returnCode;

        return ['out' => $this->lastBahubCommandResponse, 'exit_code' => $returnCode];
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

    //
    // Browser
    //

    protected function iWait(): void
    {
        usleep((int) (0.5 * 1000000));

        $this->getSession()->getPage()->waitFor(
            30,
            function () {
                $element = $this->getSession()->getPage()->find('css', 'div[class="loader-overlay"]');

                return !$element || !$element->isVisible();
            }
        );
    }

    protected function fillFieldByCSS(string $selector, string $value): void
    {
        $input = $this->getSession()->getPage()->find('css', $selector);
        $input->click();

        $input->setValue($value);
        $input->blur();
        $input->click();
    }

    /**
     * @When I press :buttonTitle button
     *
     * @param string $buttonTitle
     */
    public function iPressButton(string $buttonTitle): void
    {
        $this->pressButton($buttonTitle);
        $this->iWait();
    }

    public function clickLink($link)
    {
        try {
            parent::clickLink($link);
            $this->iWait();

        } catch (ElementNotFoundException $exception) {
            $input = $this->findByCSS('[alt="' . $link . '"], [data-field="' . $link . '"]');

            if ($input) {
                $input->click();
                $this->iWait();

                return;
            }

            throw $exception;
        }
    }

    public function selectOption($select, $option)
    {
        $this->iWait();

        try {
            parent::selectOption($select, $option);

        } catch (ElementNotFoundException $exception) {
            $input = $this->findByCSS('[alt="' . $select . '"], [data-field="' . $select . '"]');

            if ($input) {
                $input->click();
                $input->selectOption($option);

                return;
            }

            throw $exception;
        }
    }

    public function checkOption($option)
    {
        try {
            parent::checkOption($option);
            $this->iWait();

        } catch (ElementNotFoundException $exception) {
            $this->clickLink($option);
        }
    }

    protected function findByCSS(string $selector, int $timeout = 10): NodeElement
    {
        $callback = function () use ($selector) {
            return $this->getSession()->getPage()->find('css', $selector);
        };

        do {
            $element = $callback();

            if ($element instanceof NodeElement) {
                return $element;
            }

            $timeout--;
            sleep(1);

        } while (!$element instanceof NodeElement && $timeout > 0);

        sleep(5);
        throw new ElementNotFoundException($this->getSession()->getDriver(), null, 'css', $selector);
    }

    /**
     * Improved version of fillField() that supports buttons marked with "data-field" attribute
     *
     * @param string $field
     * @param string $value
     *
     * @throws ElementNotFoundException
     */
    public function fillField($field, $value)
    {
        try {
            parent::fillField($field, $value);

        } catch (ElementNotFoundException $exception) {
            $element = $this->findByCSS('input[data-field="' . $field . '"], input[placeholder="' . $field . '"]');

            if ($element) {
                $element->setValue($value);
                return;
            }

            throw $exception;
        }
    }

    /**
     * @Then I debug
     */
    public function iWaitForUserInput(): void
    {
        readline('Press [enter] to continue');
    }

    /**
     * @Then I should see message :message
     *
     * @param string $message
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeNotificationWithMessage(string $message): void
    {
        $this->findByCSS('span[data-notify="message"]:contains("' . $message . '")');
    }

    /**
     * @Then I should not see message :message
     *
     * @param string $message
     *
     * @throws LogicException
     */
    public function iShouldNotSeeNotificationWithMessage(string $message): void
    {
        $this->iWait();

        try {
            $this->findByCSS('span[data-notify="message"]:contains("' . $message . '")', 1);
        } catch (ElementNotFoundException $exception) {
            return;
        }

        throw new LogicException('Message "' . $message . '" should not be present, but actually it is');
    }

    /**
     * @When I prepare to confirm the prompt with :value
     *
     * @param string $value
     *
     * @throws DriverException
     * @throws UnsupportedDriverActionException
     */
    public function iConfirmPrompt(string $value): void
    {
        $this->getSession()->getDriver()->executeScript(
            'window.prompt = function () { return "' . $value . '"; };'
        );
    }

    /**
     * @When I close the popped modal
     *
     * @throws ElementNotFoundException
     */
    public function iExitModal(): void
    {
        $this->findByCSS('div[class="vm--overlay"]')->click();
        $this->iWait();
    }
}
