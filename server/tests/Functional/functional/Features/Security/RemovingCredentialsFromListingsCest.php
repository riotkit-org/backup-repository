<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

use FunctionalTester;

/**
 * FEATURE: Listings/search endpoints should not list full credentials eg. token ids
 */
class RemovingCredentialsFromListingsCest
{
    private const SOME_EXAMPLE_TOKEN = '79d0d931-8b52-416a-a98c-1c446818459b';
    private const RESTRICTED_TOKEN = 'e155278c-05d2-434a-87d2-c4f573e84376';

    public function seedApplication(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->createUser(['roles' => ['upload.all'], 'id' => self::SOME_EXAMPLE_TOKEN], false);

        // non-privileged token we want to use to browse tokens in listing endpoint
        $I->createUser(['roles' => ['security.search_for_tokens', 'security.authentication_lookup', 'security.cannot_see_full_token_ids'], 'id' => self::RESTRICTED_TOKEN], false);
    }

    /**
     * Case: As restricted user I should be able to search tokens by their visible parts
     *
     * @param FunctionalTester $I
     */
    public function asARestrictedUserIShouldNotBeAbleToSeeFullTokensToNotStealOthersIdentity(FunctionalTester $I): void
    {
        $I->amUser(self::RESTRICTED_TOKEN);

        // search for token by it's visible part
        $I->searchForUsers('31', 1, 50);
        $I->cantSeeResponseContains(self::SOME_EXAMPLE_TOKEN);
        $I->canSeeResponseContains('*****931-**52-**6a-**8c-********459b');
    }

    /**
     * Case: Restricted user should not be able to search under hidden parts of the token (with "*****")
     *
     * @param FunctionalTester $I
     */
    public function asARestrictedUserIShouldNotBeAbleToSearchByHiddenPartsOfTheToken(FunctionalTester $I): void
    {
        $I->amUser(self::RESTRICTED_TOKEN);

        // search for token by part that is NOT visible
        $I->searchForUsers('79d0d', 1, 50);
        $I->cantSeeResponseContains(self::SOME_EXAMPLE_TOKEN);
        $I->cantSeeResponseContains('*****931-**52-**6a-**8c-********459b');
    }

    /**
     * Case: User types full token id, then the token for sure will be found
     *
     * @param FunctionalTester $I
     */
    public function asARestrictedUserIShouldBeAbleToFindATokenAnywayIfIKnowItsFullForm(FunctionalTester $I): void
    {
        $I->amUser(self::RESTRICTED_TOKEN);

        // search for token by its full form
        $I->searchForUsers(self::SOME_EXAMPLE_TOKEN, 1, 50);
        $I->cantSeeResponseContains(self::SOME_EXAMPLE_TOKEN);
        $I->canSeeResponseContains('*****931-**52-**6a-**8c-********459b');
    }
}
