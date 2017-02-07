<?php declare(strict_types=1);

namespace Tests\Controllers\Download;

use Tests\WolnosciowiecTestCase;

/**
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

        ob_start();
        $client = $this->createClient();
        $client->request('GET', '/public/download/' . $fileName);

        $contents = ob_get_contents();
        ob_end_clean();

        $this->assertSame('test', $contents);
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