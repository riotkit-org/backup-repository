<?php declare(strict_types=1);

namespace Tests\Functional;

require_once __DIR__ . '/../Urls.php';

use FunctionalTester;

class TechnicalCest
{
    public function testCanListRoutesAsGuest(FunctionalTester $I): void
    {
        $I->sendGET('/repository/routing/map');
        $I->canSeeResponseCodeIs(200);
        $I->canSeeResponseContains('"methods":');
    }

    public function testMainPageWillReturnSuccessResponse(FunctionalTester $I): void
    {
        $I->sendGET('/');
        $I->canSeeResponseCodeIs(200);
    }
}
