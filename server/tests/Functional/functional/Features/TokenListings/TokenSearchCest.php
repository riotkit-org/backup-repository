<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

use FunctionalTester;

/**
 * FEATURE: Searching tokens by id and by metadata
 *
 * @group Domain/Authentication
 */
class TokenSearchCest
{
    private const USERS_COUNT = 50;

    public function seedApplicationWithData(FunctionalTester $I): void
    {
        $I->amAdmin();

        for ($num = 1; $num <= self::USERS_COUNT; $num++) {
            $I->createStandardUser([
                'roles' => [
                    'upload.all', 'upload.enforce_tags_selected_in_token'
                ],
                'data' => [
                    'tags' => ['role_generated_' . $num]
                ]
            ], false);
            $I->storeIdAs('.user.id', 'USER_' . $num);
        }
    }

    public function checkPagesCountIsValid(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('upload.enforce_tags_selected_in_token', 1, 10);
        $I->canSeeResponseContainsJson([
            'context' => [
                'pagination' => [
                    'page'         => 1,
                    'perPageLimit' => 10,
                    'maxPages'     => 5
                ]
            ]
        ]);
    }

    public function validateEachPageInPagination(FunctionalTester $I): void
    {
        $I->amAdmin();

        $expectedCount = self::USERS_COUNT;
        $actual        = 0;

        // 26 pages
        for ($pageNum = 1; $pageNum <= 30; $pageNum++) {
            $I->searchForUsers('upload.enforce_tags_selected_in_token', $pageNum, 10);

            $tokens = $I->grabDataFromResponseByJsonPath('.data')[0] ?? [];
            $I->canSeeResponseContainsJson([
                'context' => [
                    'pagination' => [
                        'page' => $pageNum
                    ]
                ]
            ]);
            $actual += count($tokens);
        }

        if ($expectedCount !== $actual) {
            throw new \Exception('Failed asserting that in summary there are ' . $actual . ' tokens in the database, expected exactly ' . $expectedCount);
        }
    }

    public function shouldHaveNoAccessIfNoRoleGranted(FunctionalTester $I): void
    {
        $I->amAdmin();

        // switch token to non-privileged that does not contain a role required for search endpoint
        $user = $I->createStandardUser([
            'roles' => [
                'collections.create_new'
            ]
        ]);
        $I->amUser($user->email, $user->password);
        $I->searchForUsers('upload.enforce_tags_selected_in_token', 1, 10);
        $I->canSeeResponseCodeIs(403);
    }

    public function shouldBeAbleToPerformSearchWithMinimumRequiredPermissions(FunctionalTester $I): void
    {
        $I->amAdmin();
        $user = $I->createStandardUser([
            'roles' => [
                'security.search_for_tokens',
                'security.authentication_lookup'
            ]
        ]);
        $I->amUser($user->email, $user->password);
        $I->searchForUsers('', 1, 50);
        $I->canSeeResponseCodeIs(200);
    }

    public function validateCannotPerformTooBigSearch(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('', 1, 2000);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('query_limit_too_high_use_pagination');
    }

    public function validateThePageParameter(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('', -5, 5);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('invalid_page_value');
    }

    public function validateLimitCannotBeNegative(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('', 1, -10);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('value_cannot_be_negative');
    }
}
