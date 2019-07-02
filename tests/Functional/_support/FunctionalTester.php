<?php

use Tests\Urls;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    public function amAdmin(): void
    {
        $this->amToken('test-token-full-permissions');
    }

    public function amToken(string $token): void
    {
        $this->haveHttpHeader('token', $token);
    }

    public function postJson(string $url, $params = null, $files = []): void
    {
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->sendPOST($url, $params, $files);
    }

    public function lookupToken(string $tokenId): void
    {
        $this->sendGET(
            $this->fill(
                Urls::URL_TOKEN_LOOKUP,
                ['token' => $tokenId]
            )
        );
    }

    public function createToken(array $data): string
    {
        $this->postJson(Urls::URL_TOKEN_GENERATE,
            \array_merge(
                [
                    'roles' => [],
                    'data' => []
                ],
                $data
            )
        );

        return $this->grabDataFromResponseByJsonPath('.tokenId')[0] ?? '';
    }

    public function deleteToken(string $tokenId): void
    {
        $this->sendDELETE(
            $this->fill(
                Urls::URL_TOKEN_DELETE,
                ['token' => $tokenId]
            )
        );
    }

    public function uploadByUrl(string $url, array $overrideParams = []): void
    {
        $templateParams = [
            'fileUrl' => $url,
            'tags'    => [],
            'public'  => true
        ];

        $params = \array_merge(
            $templateParams,
            ['fileUrl' => $url],
            $overrideParams
        );

        $this->sendPOST(Urls::URL_REPOSITORY_UPLOAD_BY_URL, $params);
    }
}
