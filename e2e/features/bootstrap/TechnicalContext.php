<?php declare(strict_types=1);

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

    protected function fillFieldByCSS(string $selector, string $value)
    {
        $input = $this->getSession()->getPage()->find('css', $selector);
        $input->click();

        $input->setValue($value);
        $input->blur();
        $input->click();
    }
}
