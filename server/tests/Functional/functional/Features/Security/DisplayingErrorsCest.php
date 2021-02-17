<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

/**
 * @group Security
 */
class DisplayingErrorsCest
{
    public function shouldSeeInternalServerErrorMessageFormattedAsJson(\FunctionalTester $I): void
    {
        $I->sendGET('/test/errors/500');
        $I->canSeeResponseCodeIs(500);
        $I->canSeeResponseContainsJson([
            "error" => "Internal server error",
            "code"  => 500,
            "type"  => "app.fatal-error"
        ]);
    }
}
