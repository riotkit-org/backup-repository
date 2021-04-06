<?php declare(strict_types=1);

namespace Helper;

use Codeception\TestInterface;
use GuzzleHttp\Client;
use Codeception\Module\REST;

class ApiTester extends REST
{
    use StoreTrait;
    use TemplatingTrait;

    private static string $lastFileName = '';

    public function _before(TestInterface $test)
    {
        $current = $test->getMetadata()->getFilename();
        $last    = self::$lastFileName;

        parent::_before($test);

        if ($current !== $last) {
            self::$lastFileName = $current;
            $this->restoreDatabase();
            $this->backupDatabase();
            $this->clearTheStore();
        }
    }

    public function _beforeSuite($settings = [])
    {
        parent::_beforeSuite($settings);
        $this->backupDatabase();
    }

    public function _afterSuite()
    {
        parent::_afterSuite();
        $this->restoreDatabase();
    }

    private function backupDatabase(): void
    {
        $this->sendGETUsingGuzzle('/db/backup');
    }

    private function restoreDatabase(): void
    {
        $this->sendGETUsingGuzzle('/db/restore');
    }

    /**
     * Does not use internal HTTP client, as the internal one needs to be initialized first
     *
     * @param string $path
     */
    private function sendGETUsingGuzzle(string $path)
    {
        $client = new Client();
        $client->get('http://localhost:8080' . $path, ['headers' => ['Token' => 'test-token-full-permissions']]);
    }
}
