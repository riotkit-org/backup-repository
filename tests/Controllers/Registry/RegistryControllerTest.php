<?php declare(strict_types=1);

namespace Tests\Controllers\Registry;

use Manager\FileRegistry;
use Tests\WolnosciowiecTestCase;

/**
 * @see RegistryController
 * @package Tests\Controllers\Registry
 */
class RegistryControllerTest extends WolnosciowiecTestCase
{
    /**
     * @see RegistryController::checkExistsAction()
     */
    public function testCheckExistsAction()
    {
        $this->prepareDatabase();

        /** @var FileRegistry $fileManager */
        $fileManager = $this->app->offsetGet('manager.file_registry');
        file_put_contents(__DIR__ . '/../../../web/storage/6f297f45-phpunit-test.txt', 'Hello world');

        // register the file in the registry
        $file = $fileManager->registerByName('phpunit-test.txt', 'text/plain');

        $client = $this->createClient();
        $client->request(
            'POST',
            '/repository/image/exists?_token=' . $this->getAdminToken() . '&file_name=' . $file->getFileName()
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($response['success']);
        $this->assertSame('CheckExistAction', $response['action']);
        $this->assertContains('6f297f45-phpunit-test.txt', $response['data']['url']);
        $this->assertSame('6f297f45-phpunit-test.txt', $response['data']['name']);
        $this->assertSame('text/plain', $response['data']['mime']);
        $this->assertSame('3e25960a79dbc69b674cd4ec67a72c62', $response['data']['hash']);
        $this->assertContains(date('Y-m-d'), $response['data']['date']);

        return '6f297f45-phpunit-test.txt';
    }

    /**
     * @depends testCheckExistsAction
     * @see RegistryController::deleteAction()
     * @param string $fileName
     */
    public function testDeleteAction(string $fileName)
    {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/repository/image/delete?_token=' . $this->getAdminToken() . '&file_name=' . $fileName
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($response['success']);
        $this->assertSame('DeleteAction', $response['action']);
        $this->assertSame('3e25960a79dbc69b674cd4ec67a72c62', $response['data']['hash']);
    }

    public function testFailureDeleteAction()
    {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/repository/image/delete?_token=' . $this->getAdminToken() . '&file_name=invalid-file-name'
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertFalse($response['success']);
        $this->assertSame('DeleteAction', $response['action']);
    }
}
