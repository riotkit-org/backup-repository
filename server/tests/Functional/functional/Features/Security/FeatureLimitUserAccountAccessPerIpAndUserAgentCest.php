<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

/**
 * FEATURE: GIVEN a token can have assigned a whitelist IP addresses and/or whitelist User Agents
 *          THEN a request to application using this token needs to be performed from allowed IP and/or UA
 *
 * @group Domain/Authentication
 * @group Security
 */
class FeatureLimitUserAccountAccessPerIpAndUserAgentCest
{
    private function createUser(\FunctionalTester $I, array $ua = [], array $ips = []): \User
    {
        return $I->havePermissions(['security.authentication_lookup'], [
            'data' => [
                'allowedUserAgents'  => $ua,
                'allowedIpAddresses' => $ips
            ]
        ]);
    }

    public function testUserAgentIsCheckedWhenNotPresentInRequest(\FunctionalTester $I): void
    {
        $user = $this->createUser($I, ['Test UA :)']);
        $I->seeResponseCodeIsSuccessful();

        $I->deleteHeader('User-Agent');
        $I->lookupUser($user->id);
        $I->canSeeResponseCodeIsClientError();
    }

    public function testInvalidUserAgentSentInRequest(\FunctionalTester $I): void
    {
        $user = $this->createUser($I, ['Test UA :)']);
        $I->seeResponseCodeIsSuccessful();

        $I->haveHttpHeader('User-Agent', 'Not a valid UA');
        $I->lookupUser($user->id);
        $I->canSeeResponseCodeIsClientError();
    }

    public function testWillAllowToPerformARequestWhenUserAgentMatches(\FunctionalTester $I): void
    {
        $user = $this->createUser($I, ['Test UA :)']);
        $I->seeResponseCodeIsSuccessful();

        $I->haveHttpHeader('User-Agent', 'Test UA :)');
        $I->lookupUser($user->id);
        $I->canSeeResponseCodeIsSuccessful();
    }

    public function testWillWorkWithoutLimitingUserAgentOrIPAddress(\FunctionalTester $I): void
    {
        $user = $this->createUser($I);
        $I->seeResponseCodeIsSuccessful();

        $I->deleteHeader('User-Agent');
        $I->lookupUser($user->id);
        $I->canSeeResponseCodeIsSuccessful();
    }
}
