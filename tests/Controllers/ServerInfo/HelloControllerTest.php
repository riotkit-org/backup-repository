<?php declare(strict_types=1);

namespace Tests\Controllers;

use Tests\WolnosciowiecTestCase;

/**
 * @see HelloController
 * @package Tests\Controllers
 */
class HelloControllerTest extends WolnosciowiecTestCase
{
    /**
     * Test with valid token
     */
    public function testViewAction()
    {
        $client = $this->createClient();
        $client->request('GET', '/?_token=' . $this->getAdminToken());

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(200, $response['code']);
        $this->assertContains('Hello, welcome.', $response['message']);
        $this->assertRegExp('/[0-9\.]/', $response['version']['version']);
        $this->assertRegExp('/[0-9\.]/', $response['version']['release']);
    }

    /**
     * @dataProvider invalidTokensProvider
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param string $token
     */
    public function testInvalidViewAction(string $token)
    {
        $client = $this->createClient();
        $client->request('GET', '/?_token=' . $this->prepareToken($token));
    }
}
