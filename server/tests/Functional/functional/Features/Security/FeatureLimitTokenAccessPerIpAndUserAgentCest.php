<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

/**
 * FEATURE: GIVEN a token can have assigned a whitelist IP addresses and/or whitelist User Agents
 *          THEN a request to application using this token needs to be performed from allowed IP and/or UA
 */
class FeatureLimitTokenAccessPerIpAndUserAgentCest
{
    private function createToken(\FunctionalTester $I, array $ua = [], array $ips = []): string
    {
        return $I->haveRoles(['security.authentication_lookup'], [
            'data' => [
                'allowedUserAgents'  => $ua,
                'allowedIpAddresses' => $ips
            ]
        ]);
    }

    public function testUserAgentIsCheckedWhenNotPresentInRequest(\FunctionalTester $I): void
    {
        $token = $this->createToken($I, ['Test UA :)']);
        $I->seeResponseCodeIsSuccessful();

        $I->deleteHeader('User-Agent');
        $I->lookupToken($token);
        $I->canSeeResponseCodeIsClientError();
    }

    public function testInvalidUserAgentSentInRequest(\FunctionalTester $I): void
    {
        $token = $this->createToken($I, ['Test UA :)']);
        $I->seeResponseCodeIsSuccessful();

        $I->haveHttpHeader('User-Agent', 'Not a valid UA');
        $I->lookupToken($token);
        $I->canSeeResponseCodeIsClientError();
    }

    public function testWillAllowToPerformARequestWhenUserAgentMatches(\FunctionalTester $I): void
    {
        $token = $this->createToken($I, ['Test UA :)']);
        $I->seeResponseCodeIsSuccessful();

        $I->haveHttpHeader('User-Agent', 'Test UA :)');
        $I->lookupToken($token);
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function testWillWorkWithoutLimitingUserAgentOrIPAddress(\FunctionalTester $I): void
    {
        $token = $this->createToken($I);
        $I->seeResponseCodeIsSuccessful();

        $I->deleteHeader('User-Agent');
        $I->lookupToken($token);
        $I->canSeeResponseCodeIsSuccessful();
    }
}
