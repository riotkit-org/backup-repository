<?php declare(strict_types=1);

namespace Tests\Controllers\Download;

use Tests\WolnosciowiecTestCase;

/**
 * @see FileServeController
 * @package Tests\Controllers
 */
class ImageServeControllerTest extends WolnosciowiecTestCase
{
    /**
     * Successful case
     */
    public function testDownloadAction()
    {
        $this->prepareDatabase();
        $fileName = $this->putExampleFile();

        $client = $this->createClient();
        $client->request('GET', '/public/download/' . $fileName);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Failure case
     */
    public function testFailureDownloadAction()
    {
        $client = $this->createClient();
        $client->request('GET', '/public/download/this-file-does-not-exists');

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertFalse($response['success']);
        $this->assertSame(404, $response['code']);
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}