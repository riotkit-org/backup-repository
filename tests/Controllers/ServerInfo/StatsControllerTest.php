<?php declare(strict_types=1);

namespace Tests\Controllers;

use Tests\WolnosciowiecTestCase;

/**
 * @see StatsController
 * @package Tests\Controllers
 */
class StatsControllerTest extends WolnosciowiecTestCase
{
    /**
     * Test with valid token
     */
    public function testViewAction()
    {
        $client = $this->createClient();
        $client->request('GET', '/repository/stats?_token=' . $this->getAdminToken());

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertTrue($response['success']);
        $this->assertInternalType('array', $response['data']);
        $this->assertInternalType('array', $response['data']['disk_space']);
        $this->assertInternalType('array', $response['data']['avg_load']);
        $this->assertInternalType('array', $response['data']['storage']);
        $this->assertInternalType('integer', $response['data']['storage']['elements_count']);

        foreach ($response['data']['avg_load'] as $load) {
            $this->assertInternalType('float', $load);
        }
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
        $client->request('GET', '/repository/stats?_token=' . $this->prepareToken($token));
    }
}
