<?php declare(strict_types=1);

namespace Tests\Functional\Features\Security;

/**
 * FEATURE: GIVEN the token is assigned a restriction role, THEN only one file can be uploaded using this token
 */
class FeatureOnlyOneFileAllowedToUploadCest
{
    public function testOnlyOneFileCanBeUploaded(\FunctionalTester $I): void
    {
        $I->haveRoles(['upload.all', 'upload.only_once_successful']);
        $I->stopFollowingRedirects();

        // upload first file
        $I->uploadByPayload('
            A tenant in Eastbourne has won back £100 by challenging deductions to his deposit. 
            The tenant’s landlord was attempting to make various deductions for replacement items 
            that had been subject to fair wear and tear.',
            ['fileName' => 'SolFed_article_1.txt']
        );
        $I->canSeeResponseCodeIsSuccessful();

        // try to upload second file
        $I->uploadByPayload('
            Kevin Karam, the son of Hamid Karam, has made multiple long and rambling posts about us on Facebook, 
            calling SolFed a “gang”, making wildly contradictory statements about the worker’s status at CJ Barbers, 
            referring to SolFed in general as a “bunch of benefit losers”, etc. It\'s nothing particularly remarkable -
             the usual smear campaigns that bosses and their friends try when they are backed into a corner.',
            ['fileName' => 'SolFed_article_2.txt']
        );
        $I->canSeeResponseCodeIs(403);
    }
}
