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
                'permissions' => [
                    'upload.all', 'upload.enforce_tags_selected_in_user_account'
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
        $I->searchForUsers('upload.enforce_tags_selected_in_user_account', 1, 10);
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
            $I->searchForUsers('upload.enforce_tags_selected_in_user_account', $pageNum, 10);

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

    public function shouldHaveNoAccessIfNoPermissionGranted(FunctionalTester $I): void
    {
        $I->amAdmin();

        // switch token to non-privileged that does not contain a permission required for search endpoint
        $user = $I->createStandardUser([
            'permissions' => [
                'collections.create_new'
            ]
        ]);
        $I->amUser($user->email, $user->password);
        $I->searchForUsers('upload.enforce_tags_selected_in_user_account', 1, 10);
        $I->canSeeResponseCodeIs(403);
    }

    public function shouldBeAbleToPerformSearchWithMinimumRequiredPermissions(FunctionalTester $I): void
    {
        $I->amAdmin();
        $user = $I->createStandardUser([
            'permissions' => [
                'security.search_for_users',
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
        $I->canSeeResponseContainsJson([
            "fields" => [
                "query" => [
                    "message" => "Limit is too high",
                    "code"    => 50003
                ]
            ],
            "type" => "validation.error"
        ]);
    }

    public function validateThePageParameter(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('', -5, 5);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContainsJson([
            "fields" => [
                "page" => [
                    "message" => "Page cannot be negative",
                    "code"    => 50005
                ]
            ],
            "type" => "validation.error"
        ]);
    }

    public function validateLimitCannotBeNegative(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('', 1, -10);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContainsJson([
            "fields" => [
                "query" => [
                    "message" => "Limit is too low",
                    "code"    => 50004
                ]
            ],
            "type" => "validation.error"
        ]);
    }

    public function validateMultipleParametersAreCheckedAtOnce(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForUsers('', -10, -10);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContainsJson([
            "fields" => [
                "query" => [
                    "message" => "Limit is too low",
                    "code"    => 50004
                ],
                "page" => [
                    "message" => "Page cannot be negative",
                    "code"    => 50005
                ]
            ],
            "type" => "validation.error"
        ]);
    }
}
