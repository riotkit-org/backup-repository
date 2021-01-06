<?php declare(strict_types=1);

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit\Framework\Assert as Assertions;

class TechnicalContext extends MinkContext
{
    private string $lastShellCommandResponse = '';
    private int $lastShellCommandExitCode = 1;

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
}
