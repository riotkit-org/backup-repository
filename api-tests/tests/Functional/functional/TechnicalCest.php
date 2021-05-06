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

    public function testInfluxDBMetricsAreAccessibleWithoutJWTButSecuredWithConfigurableCode(FunctionalTester $I): void
    {
        $I->sendGET('/metrics/backup_repository_report/influxdb?code=test');
        $I->seeResponseContains('backup_repository_report,base_url="http://server",app_env=');
    }

    public function testInfluxDBMetricsAreAccessibleOnlyOnValidCode(FunctionalTester $I): void
    {
        $I->sendGET('/metrics/backup_repository_report/influxdb');
        $I->canSeeResponseCodeIs(403);
    }
}
