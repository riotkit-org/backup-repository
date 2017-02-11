<?php declare(strict_types=1);

namespace Tests\Controllers;

use Model\Permissions\Roles;
use Tests\WolnosciowiecTestCase;

/**
 * @package Tests\Controllers
 */
class TokenGenerationControllerTest extends WolnosciowiecTestCase
{
    /**
     * Case: Success
     *
     * @see TokenGenerationController::generateTemporaryTokenAction()
     * @return string
     */
    public function testSuccessGenerateTemporaryTokenAction()
    {
        $this->prepareDatabase();

        $client = $this->createClient();
        $client->request('POST', '/auth/token/generate?_token=' . $this->getAdminToken(), [], [], [], json_encode(
            [
                'roles' => [Roles::ROLE_UPLOAD_IMAGES],
                'data'  => [
                    'tags' => ['test_upload'],
                ]
            ]
        ));

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($response['success']);
        $this->assertGreaterThan(time(), strtotime($response['data']['expires']));
        $this->assertNotEmpty($response['data']['tokenId']);

        return $response['data']['tokenId'] ?? '';
    }

    /**
     * @return array
     */
    public function failureDataProvider()
    {
        return [
            'Valid token, no roles selected' => [
                self::ADMIN_TOKEN,
                [],
                'No roles specified, please specify roles in the POST body as JSON eg. {"roles": ["role1", "role2"], "data": {}}',
            ],

            'Invalid token, roles provided' => [
                '',
                [Roles::ROLE_UPLOAD_IMAGES],
                'Access denied, please verify the "_token" parameter',
            ],

            'Invalid token, no roles provided' => [
                '',
                [],
                'Access denied, please verify the "_token" parameter',
            ]
        ];
    }

    /**
     * Case: failure
     *
     * @dataProvider failureDataProvider()
     *
     * @param string $token
     * @param array  $roles
     * @param string $errorMessage
     */
    public function testFailureGenerateTemporaryTokenAction(string $token, array $roles, string $errorMessage = '')
    {
        $token = $this->prepareToken($token);
        $this->prepareDatabase();

        $client = $this->createClient();

        try {
            $client->request('POST', '/auth/token/generate?_token=' . $token, [], [], [], json_encode($roles));
            $response = json_decode($client->getResponse()->getContent(), true);
        }
        catch (\Exception $e) {
            $this->assertSame($errorMessage, $e->getMessage());
            return;
        }

        $this->assertFalse($response['success']);
        $this->assertSame($errorMessage, $response['error']);
    }
}
