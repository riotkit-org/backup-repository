<?php declare(strict_types=1);

namespace Tests\Controllers\UserForm;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tests\WolnosciowiecTestCase;

/**
 * @see ImageUploadFormController
 * @package Tests\Controllers
 */
class ImageUploadFormControllerTest extends WolnosciowiecTestCase
{
    /**
     * @see ImageUploadFormController::showFormAction()
     */
    public function testShowFormAction()
    {
        $this->prepareDatabase();

        $client = $this->createClient();
        $client->request('GET', '/public/upload/image/form?_token=' . $this->prepareToken(self::IMAGE_UPLOAD_TOKEN));

        $this->assertContains('Pick an image to upload and crop', $client->getResponse()->getContent());
    }

    /**
     * @return array
     */
    public function provideInvalidTokens()
    {
        return [
            'No token' => [
                '',
            ],

            'Invalid role' => [
                self::RANDOM_ROLE_TOKEN,
            ],
        ];
    }

    /**
     * @dataProvider provideInvalidTokens()
     * @param string $token
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testNoAccessShowFormAction(string $token)
    {
        $this->prepareDatabase();

        $client = $this->createClient();
        $client->request('GET', '/public/upload/image/form?_token=' . $this->prepareToken($token));
    }

    /**
     * @return array
     */
    private function getExamplePayload()
    {
        return [
            'content'  => 'data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/../../../../docs/images/anarchosyndicalism.png')),
            'fileName' => 'anarchosyndicalism.png',
            'mimeType' => 'image/png',
        ];
    }

    /**
     * @see ImageUploadFormController::uploadAction()
     */
    public function testUploadAction()
    {
        $this->prepareDatabase();

        $client = $this->createClient();
        $client->request(
            'POST',
            '/public/upload/image?_token=' . $this->prepareToken(self::IMAGE_UPLOAD_TOKEN),
            [], [], [],
            json_encode($this->getExamplePayload())
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('OK', $response['status']);
        $this->assertSame(200, $response['code']);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('anarchosyndicalism.png', $response['url']);

        // clean up the file after test
        $parts = explode('/', $response['url']);
        @unlink(__DIR__ . '/../../../../web/storage/' . end($parts));
    }

    /**
     * @return array
     */
    public function failureUploadProvider()
    {
        return [
            'Missing token' => [
                '',
                $this->getExamplePayload(),
                'Access denied, please verify the "_token" parameter',
            ],

            'Token with invalid role' => [
                self::RANDOM_ROLE_TOKEN,
                $this->getExamplePayload(),
                'Access denied, please verify the "_token" parameter',
            ],

            'Invalid payload' => [
                self::ADMIN_TOKEN,
                [],
                'Upload failed',
            ],
        ];
    }

    /**
     * @dataProvider failureUploadProvider
     *
     * @param string $token
     * @param array  $payload
     * @param string $errorMessage
     */
    public function testFailureUploadAction(string $token, array $payload, string $errorMessage)
    {
        $client = $this->createClient();

        try {
            $client->request(
                'POST',
                '/public/upload/image?_token=' . $this->prepareToken($token),
                [], [], [],
                json_encode($payload)
            );

        } catch (AccessDeniedException $e) {
            $this->assertSame($errorMessage, $e->getMessage());
            return;
        }

        $this->assertContains($errorMessage, $client->getResponse()->getContent());
    }
}
