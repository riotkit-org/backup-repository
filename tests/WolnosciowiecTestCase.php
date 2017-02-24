<?php declare(strict_types=1);

namespace Tests;

use Model\Entity\Token;
use Model\Permissions\Roles;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Base test case
 *
 * @package Tests
 */
abstract class WolnosciowiecTestCase extends TestCase
{
    const ADMIN_TOKEN       = '#ADMIN_TOKEN#';
    const RANDOM_ROLE_TOKEN = '#RANDOM_ROLE_TOKEN#';
    const IMAGE_UPLOAD_TOKEN = '#TOKEN_PUBLIC_UPLOAD_IMAGES#';

    /**
     * @var HttpKernelInterface|Application $app
     */
    protected $app;

    /**
     * @var string $adminToken
     */
    private $adminToken;

    /**
     * @return string
     */
    public function getAdminToken(): string
    {
        return $this->adminToken;
    }

    /**
     * @return Application|HttpKernelInterface
     */
    public function getApp()
    {
        return $this->app;
    }

    protected function setUp()
    {
        $this->app        = $this->createApplication();
        $this->adminToken = $this->app['api.key'];
    }

    /**
     * @param string $prefix
     * @param string $content
     * @return string
     */
    public function createTemporaryFile(string $prefix, string $content)
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), $prefix);

        $pointer = fopen($tempFilePath, 'w');
        fwrite($pointer, $content);
        fclose($pointer);

        return $tempFilePath;
    }

    /**
     * @return array
     */
    public function invalidTokensProvider()
    {
        return [
            'Empty token' => [
                '',
            ],

            'Token with random role' => [
                self::RANDOM_ROLE_TOKEN,
            ],
        ];
    }

    /**
     * Workaround for putting the token in data provider
     * where the container is not available yet
     *
     * @param string $inputToken
     * @return string
     */
    public function prepareToken(string $inputToken)
    {
        if ($inputToken === self::ADMIN_TOKEN) {
            return $this->getAdminToken();
        }

        elseif ($inputToken === self::IMAGE_UPLOAD_TOKEN) {
            return $this->generateToken([Roles::ROLE_UPLOAD_IMAGES], new \DateTime('2030-05-05'));
        }

        elseif ($inputToken === self::RANDOM_ROLE_TOKEN) {
            return $this->generateToken([(string)rand(1, 99999)], new \DateTime('2030-05-05'));
        }

        return $inputToken;
    }

    private function generateToken(array $roles, \DateTime $expiration)
    {
        /** @var Token $token */
        $token = $this->app->offsetGet('manager.token')
            ->generateNewToken($roles, $expiration);

        return $token->getId();
    }

    /**
     * @return string
     */
    public function putExampleFile()
    {
        $path = $this->app->offsetGet('manager.storage')->getStoragePath() . '/test.txt';
        file_put_contents($path, 'test');

        return 'test.txt';
    }

    /**
     * Creates the application.
     *
     * @return HttpKernelInterface|Application
     */
    public function createApplication()
    {
        @define('ENV', 'test');

        /** @var HttpKernelInterface|Application $app */
        $app = require __DIR__.'/../src/app.php';
        require __DIR__.'/../config/dev.php';
        require __DIR__.'/../src/services.php';
        require __DIR__.'/../src/controllers.php';
        $app['session.test'] = true;

        $app['debug'] = true;
        unset($app['exception_handler']);

        $app->boot();
        $app->flush();

        return $this->app = $app;
    }

    /**
     * Prepare the test database by removing all data
     */
    public function prepareDatabase()
    {
        shell_exec('./src/console.php database:erase --env=test');
    }

    /**
     * Creates a Client.
     *
     * @param array $server Server parameters
     *
     * @return Client A Client instance
     */
    public function createClient(array $server = array())
    {
        if (!class_exists('Symfony\Component\BrowserKit\Client')) {
            throw new \LogicException('Component "symfony/browser-kit" is required by WebTestCase.'.PHP_EOL.'Run composer require symfony/browser-kit');
        }

        return new Client($this->app, $server);
    }
}