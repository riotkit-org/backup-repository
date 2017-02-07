<?php declare(strict_types=1);

namespace Tests\Controllers;

use Tests\WolnosciowiecTestCase;

/**
 * @see RoutingMapController
 * @package Tests\Controllers
 */
class RoutingMapControllerTest extends WolnosciowiecTestCase
{
    /**
     * Test with valid token
     *
     * @see RoutingMapController::viewAction()
     */
    public function testViewAction()
    {
        $client = $this->createClient();
        $client->request('GET', '/repository/routing/map?_token=' . $this->getAdminToken());

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(200, $response['code']);
        $this->assertInternalType('array', $response['data']);

        foreach ($response['data'] as $route) {
            $this->assertArrayHasKey('methods', $route);
            $this->assertInternalType('array', $route);
            $this->assertArrayHasKey('path', $route);
            $this->assertInternalType('string', $route['path']);
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
        $client->request('GET', '/repository/routing/map?_token=' . $this->prepareToken($token));
    }
}
