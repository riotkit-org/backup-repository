<?php declare(strict_types=1);

namespace Tests\Functional;

use FunctionalTester;

class BackupCollectionUploadRotationStrategyCest
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    public function prepareDataForTest(FunctionalTester $I): void
    {
        $I->amAdmin();
        $this->token = $I->createToken([
            'roles' => [
                "collections.create_new",
                "collections.manage_tokens_in_allowed_collections",
                "collections.upload_to_allowed_collections",
                "collections.list_versions_for_allowed_collections",
                "upload.backup"
            ]
        ]);

        $I->amToken($this->token);

        $this->id = $I->createCollection([
            "maxBackupsCount" => 2,
            "maxOneVersionSize" => "1MB",
            "maxCollectionSize" => "5MB",
            "strategy" => "delete_oldest_when_adding_new",
            "description" => "Title: Solidarity with Postal Workers, Against State Repression!",
            "password" => "solidarity-forever",
            "filename" => "solidarity-with-postal-workers-article"
        ]);
    }

    public function testFirstVersionWasUploaded(FunctionalTester $I): void
    {
        $I->amToken($this->token);
        $I->uploadToCollection($this->id,
            "ZSP-IWA calls for a week of protest action against the repression of workers from the Post Office in Poland");

        $I->canSeeResponseContains('solidarity-with-postal-workers-article-v1"');
    }

    public function testUploadingFirstVersionCannotBePossible(FunctionalTester $I): void
    {
        $I->amToken($this->token);
        $I->uploadToCollection($this->id,
            "ZSP-IWA calls for a week of protest action against the repression of workers from the Post Office in Poland");

        $I->canSeeResponseContains('backup_version_uploaded_twice');
    }

    public function testUploadingSecondVersionShouldStoreTheUploadedVersion(FunctionalTester $I): void
    {
        $I->amToken($this->token);
        $I->uploadToCollection($this->id,
            "ZSP-lWA calls for a week of protest action against the repression of workers from the Post Office in Poland
                     ===========================================================================================================
        ");
        $I->canSeeResponseContains('solidarity-with-postal-workers-article-v2"');
    }

    public function testByUploadingThirdVersionTheFirstIsDeletedByRotation(FunctionalTester $I): void
    {
        $I->amToken($this->token);

        // step 4:
        $I->uploadToCollection($this->id,
            "ZSP-lWA calls for a week of protest action against the repression of workers from the Post Office in Poland
===========================================================================================================

February 26 - March 4, 2018

For almost two years, postal workers around Poland have been protesting for better working conditions. ZSP has been active in attempts to coordinate national networks, actions and to unionize workers, together with hundreds of other workers spread around the country. All of the workers shared two things in common: they are fed up with the very poor working conditions and were not satisfied with the role of the representative unions which signed various agreements on their behalf with the employer.

Major actions of the ZSP have included exposing the problems in the Post Office and providing a forum for thousands of workers to network, making contacts all over the country and organizing national meetings, promoting alternative unionism and organizing and taking part in organizing national protests, together with thousands of workers.

This work was immediately attacked by representatives both of Solidarity and by the management of the Post Office, which is a state-owned enterprise. Solidarity at the Post Office carried out a vile disinformation campaign, which sought to present the ZSP as if it was a Soviet paramilitary organization – such a grotesque misrepresentation that it would have been hilarious if it weren't such a part of the current far-right political hysteria.

When workers began to organize and call for protests on a national level, calling for a significant pay rise and other demands, the management of the Post Office, together with their union servants, started a campaign of harrassment and threats against the workers. Throughout Poland, workers were visited, told that the protests were illegal, threatened with disciplinary action, etc.

Finally, after last years' large national protests, Klaudiusz and Rafal were fired and now Zbyszek. He had been active after the protests in trying to organize workers.

Besides this retaliation against the postal workers, there have also been various attempts to bring criminal cases against another member of ZSP from Warsaw, Jakub. The Post Office tried (unsuccessfully) to have him prosecuted under the amazing charge of „organizing a bloody revolution”. Although this was so stupid it was hysterical, it is actually a very serious charge, completely out of line with the real crime – helping to coordinate the national workers protest. Unfortunately the Post Office, backed by the State, is trying to prosecute on lesser criminal charges, which are also complete nonsense.

Although our organization has born the brunt of the repression, it is important to recognize that in fact, they are repressing all workers.

In connection with the recent round of repression, an international week of solidarity and protest has been called for Febuary 26 - March 4, with some protests planned at various representations of the Polish State around the globe. More information on planned protests to follow.

If you would like to show other forms of solidarity, please contact is@zsp.net.pl.

Related articles in English:
http://zsp.net.pl/postal-workers-protest-warsaw
zsp.net.pl/no-protest-allowed-post-office
http://zsp.net.pl/postal-workers-protest-all-over-poland
http://zsp.net.pl/solidarity-postal-workers-down-bosses
zsp.net.pl/post-office-workers-make-demands
http://zsp.net.pl/third-national-meeting-postal-workers-adopt-libertaria...
http://zsp.net.pl/%E2%80%9Esolidarity%E2%80%9D-screws-postal-workers-aga...

In Polish:
http://zsp.net.pl/kampanie/poczta-polska
listonosze.pl
        ");
        $I->canSeeResponseContains('solidarity-with-postal-workers-article-v3"');

        $I->browseCollectionVersions($this->id);

        // rotation expectations
        $I->cantSeeResponseContains('solidarity-with-postal-workers-article-v1');
        $I->canSeeResponseContains('solidarity-with-postal-workers-article-v2');
        $I->canSeeResponseContains('solidarity-with-postal-workers-article-v3');
    }

    public function testICanFetchAnyVersion(FunctionalTester $I): void
    {
        $I->amAdmin();

        $I->downloadCollectionVersion($this->id, 'v3');
        $I->canSeeResponseContains('http://zsp.net.pl/kampanie/poczta-polska');

        $I->downloadCollectionVersion($this->id, 'latest');
        $I->canSeeResponseContains('http://zsp.net.pl/kampanie/poczta-polska');

        $I->downloadCollectionVersion($this->id, 'recent');
        $I->canSeeResponseContains('http://zsp.net.pl/kampanie/poczta-polska'); // equals recent and v3

        $I->downloadCollectionVersion($this->id, 'first');
        $I->cantSeeResponseContains('http://zsp.net.pl/kampanie/poczta-polska'); // equals v2

        $I->downloadCollectionVersion($this->id, 'v2');
        $I->cantSeeResponseContains('http://zsp.net.pl/kampanie/poczta-polska');
    }

    public function testICanUploadV1AgainAsV4AfterV1WasDeletedByRotation(FunctionalTester $I): void
    {
        $I->amAdmin();
        $I->uploadToCollection($this->id,
            "ZSP-IWA calls for a week of protest action against the repression of workers from the Post Office in Poland");

        $I->downloadCollectionVersion($this->id, 'v4');
        $I->cantSeeResponseContains('http://zsp.net.pl/kampanie/poczta-polska');

        $I->downloadCollectionVersion($this->id, 'v3');
        $I->canSeeResponseContains('http://zsp.net.pl/kampanie/poczta-polska');
    }
}
