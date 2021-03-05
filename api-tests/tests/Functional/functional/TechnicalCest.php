<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

/**
 * @group Technical
 */
class TechnicalCest
{
    public function testMainPageWillReturnSuccessResponse(FunctionalTester $I): void
    {
        $I->sendGET('/');
        $I->canSeeResponseCodeIs(200);
    }
}
