<?php declare(strict_types=1);

namespace Tests\Controllers;

use Tests\WolnosciowiecTestCase;

/**
 * @package Tests\Controllers
 */
class ExpiredTokensControllerTest extends WolnosciowiecTestCase
{
    /**
     * Successful case
     */
    public function testClearExpiredTokensAction()
    {
        $this->prepareDatabase();

        $client = $this->createClient();
        $client->request('GET', '/jobs/token/expired/clear?_token=' . $this->getAdminToken());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
    }

    public function provideFailureCases()
    {
        return [
            'Empty token' => [
                '',
            ],

            'Invalid token' => [
                'asdasdasdasdasd',
            ],

            'Token valid only for a specific role that does not match' => [
                self::RANDOM_ROLE_TOKEN,
            ],
        ];
    }

    /**
     * @dataProvider provideFailureCases
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @param string $token
     */
    public function testFailureClearExpiredTokensAction(string $token)
    {
        $token = $this->prepareToken($token);

        $client = $this->createClient();
        $client->request('GET', '/jobs/token/expired/clear?_token=' . $token);
    }
}