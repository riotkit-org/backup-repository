<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

use FunctionalTester;

/**
 * FEATURE: Searching tokens by id and by metadata
 */
class TokenSearchCest
{
    public function seedApplicationWithData(FunctionalTester $I): void
    {
        $I->amAdmin();

        for ($num = 1; $num <= 250; $num++) {
            $I->createToken([
                'roles' => [
                    'upload.all', 'upload.enforce_no_password', 'upload.enforce_tags_selected_in_token'
                ],
                'data' => [
                    'tags' => ['role_generated_' . $num]
                ]
            ], false);
            $I->storeIdAs('.token.id', 'TOKEN_' . $num);
        }
    }

    public function checkPagesCountIsValid(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForTokens('upload.enforce_tags_selected_in_token', 1, 10);
        $I->canSeeResponseContainsJson([
            'context' => [
                'pagination' => [
                    'page'         => 1,
                    'perPageLimit' => 10,
                    'maxPages'     => 25
                ]
            ]
        ]);
    }

    public function validateEachPageInPagination(FunctionalTester $I): void
    {
        $I->amAdmin();

        $expectedCount = 250;
        $actual        = 0;

        // 26 pages
        for ($pageNum = 1; $pageNum <= 30; $pageNum++) {
            $I->searchForTokens('upload.enforce_tags_selected_in_token', $pageNum, 10);

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
        $I->amToken(
            $I->createToken([
                'roles' => [
                    'collections.create_new'
                ]
            ])
        );
        $I->searchForTokens('upload.enforce_tags_selected_in_token', 1, 10);
        $I->canSeeResponseCodeIs(403);
    }

    public function shouldBeAbleToPerformSearchWithMinimumRequiredPermissions(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->amToken(
            $I->createToken([
                'roles' => [
                    'security.search_for_tokens',
                    'security.authentication_lookup'
                ]
            ])
        );
        $I->searchForTokens('', 1, 50);
        $I->canSeeResponseCodeIs(200);
    }

    public function validateCannotPerformTooBigSearch(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForTokens('', 1, 2000);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('query_limit_too_high_use_pagination');
    }

    public function validateThePageParameter(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForTokens('', -5, 5);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('invalid_page_value');
    }

    public function validateLimitCannotBeNegative(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->searchForTokens('', 1, -10);
        $I->canSeeResponseCodeIs(400);
        $I->canSeeResponseContains('value_cannot_be_negative');
    }
}
