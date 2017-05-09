<?php declare(strict_types=1);

namespace Tests\Controllers;

use Tests\WolnosciowiecTestCase;

/**
 * @see AddByUrlController
 * @package Tests\Controllers
 */
class AddByUrlControllerTest extends WolnosciowiecTestCase
{
    public function testUploadAction()
    {
        $this->prepareDatabase();

        $client = $this->createClient();
        $client->request(
            'POST',
            '/repository/image/add-by-url?_token=' . $this->getAdminToken(),
            [], [], [],
            json_encode([
                'fileUrl' => 'https://raw.githubusercontent.com/Wolnosciowiec/image-repository/master/docs/images/anarchosyndicalism.png',
            ])
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('OK', $response['status']);
        $this->assertSame(200, $response['code']);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('anarchosyndicalism.png', $response['url']);

        // clean up
        $parts = explode('/', $response['url']);
        @unlink(__DIR__ . '/../../../../web/storage/' . end($parts));
    }
}