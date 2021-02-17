<?php declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Tests\Urls;

require_once __DIR__ . '/../Urls.php';


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;
    use \Codeception\Util\Shared\Asserts;

    public function amAdmin(): void
    {
        $this->amGuest();
        $this->haveHttpHeader('Test-Token', 'test-token-full-permissions');
    }

    public function amGuest(): void
    {
        $this->deleteHeader('token');
        $this->deleteHeader('Test-Token');
        $this->deleteHeader('authorization');
    }

    public function amUser(string $email, string $password): void
    {
        $this->amGuest();

        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->sendPOST(Urls::URL_JWT_AUTH_LOGIN, [
            'username' => $email,
            'password' => $password
        ]);

        $this->iHaveToken($this->grabDataFromResponseByJsonPath('.token')[0] ?? '');
    }

    public function iHaveToken(string $token): void
    {
        $this->amGuest();

        $this->haveHttpHeader('token', $token);
        $this->amBearerAuthenticated($token);
    }

    public function assertSame($expected, $actual, $message = '')
    {
        \PHPUnit\Framework\Assert::assertSame($expected, $actual, $message);
    }

    public function assertTrue($condition, $message = '')
    {
        \PHPUnit\Framework\Assert::assertTrue($condition, $message);
    }

    public function haveRoles(array $roles, array $params = [], bool $assert = true): User
    {
        $this->amAdmin();

        $user = $this->createStandardUser(
            array_merge(
                ['roles' => $roles],
                $params
            ),
            $assert
        );

        $this->amUser($user->email, $user->password);

        return $user;
    }

    public function postJson(string $url, $params = null, $files = []): void
    {
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->sendPOST($url, $params, $files);
    }

    public function putJson(string $url, $params = null, $files = []): void
    {
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->sendPUT($url, $params, $files);
    }

    public function lookupUser(string $userId): void
    {
        $this->sendGET(
            $this->fill(
                Urls::URL_USER_LOOKUP,
                ['userId' => $userId]
            )
        );
    }

    public function searchForUsers(string $searchPhrase, int $page, int $limit): void
    {
        $this->sendGET(
            $this->fill(
                Urls::URL_TOKEN_SEARCH,
                ['query' => $searchPhrase, 'page' => $page, 'limit' => $limit]
            )
        );
    }

    public function createUser(array $data, bool $assert = true): User
    {
        $this->postJson(Urls::URL_USER_CREATE,
            \array_merge(
                [
                    'roles' => [],
                    'data' => []
                ],
                $data
            )
        );

        if ($assert) {
            $this->canSeeResponseCodeIs(201);
        }

        return new User(
            $this->grabDataFromResponseByJsonPath('.user.id')[0] ?? '',
            $this->grabDataFromResponseByJsonPath('.user.email')[0] ?? '',
            $data['password'] ?? ''
        );
    }

    /**
     * Creates a user with standard fields filled up like email, password, organization, about
     *
     * @param array $data
     * @param bool $assert
     *
     * @return User
     */
    public function createStandardUser(array $data, bool $assert = true): User
    {
        $data = array_merge([
            'password'     => 'food-not-bombs-1980',
            'email'        => Uuid::uuid4()->getHex() . '@riseup.net',
            'organization' => 'Food Not Bombs',
            'about'        => 'A loose-knit group of independent collectives, sharing free vegan and vegetarian food with others. Food Not Bombs\' ideology is that myriad corporate and government priorities are skewed to allow hunger to persist in the midst of abundance. To demonstrate this (and to reduce costs), a large amount of the food served by the group is surplus food from grocery stores, bakeries and markets that would otherwise go to waste (or, occasionally, has already been thrown away). This group exhibits a form of franchise activism.',
        ], $data);

        return $this->createUser($data, $assert);
    }

    public function revokeAccess(string $userId): void
    {
        $this->sendDELETE(
            $this->fill(
                Urls::URL_TOKEN_DELETE,
                ['userId' => $userId]
            )
        );
    }

    public function fetchFile(string $filename, array $params = []): void
    {
        $this->sendGET(
            $this->fill(
                Urls::URL_REPOSITORY_FETCH_FILE,
                ['fileName' => $filename]
            ),
            $params
        );
    }

    public function listFiles(array $params = []): void
    {
        $this->sendGET(Urls::URL_REPOSITORY_LISTING, $params);
    }

    public function createCollection(array $params): string
    {
        $this->postJson(Urls::URL_COLLECTION_CREATE, $params);

        return $this->grabDataFromResponseByJsonPath('collection.id')[0] ?? '';
    }

    public function updateCollection(string $id, array $params): void
    {
        $params['collection'] = $id;

        $this->putJson(Urls::URL_COLLECTION_UPDATE, $params);
    }

    public function uploadToCollection(string $id, string $contents): void
    {
        $this->sendPOST(
            $this->fill(
                Urls::URL_COLLECTION_UPLOAD,
                ['collectionId' => $id]
            ),
            $contents
        );
    }

    public function browseCollectionVersions(string $id): void
    {
        $this->sendGET(
            $this->fill(
                Urls::URL_COLLECTION_LIST_VERSIONS,
                ['collectionId' => $id]
            )
        );
    }

    public function canSeeResponseOfICanBrowseCollectionVersions(): void
    {
        $this->seeResponseContainsJson([
            'message'  => 'OK',
            'status'   => true,
            'versions' => []
        ]);
    }

    public function downloadCollectionVersion(string $id, string $version): void
    {
        $this->sendGET(
            $this->fill(
                Urls::URL_COLLECTION_DOWNLOAD_VERSION,
                ['collectionId' => $id, 'version' => $version]
            )
        );
    }

    public function deleteVersionFromCollection(string $id, string $version): void
    {
        $this->sendDELETE(
            $this->fill(
                Urls::URL_COLLECTION_DELETE_VERSION,
                ['collectionId' => $id, 'version' => $version]
            )
        );
    }

    public function deleteCollection(string $id): void
    {
        $this->sendDELETE(
            $this->fill(
                Urls::URL_COLLECTION_DELETE,
                ['id' => $id]
            )
        );
    }

    public function fetchCollection(string $id): void
    {
        $this->sendGET(
            $this->fill(
                Urls::URL_COLLECTION_FETCH,
                ['id' => $id]
            )
        );
    }

    public function searchCollectionsFor(array $params): void
    {
        $this->sendGET(Urls::URL_COLLECTION_LISTING, $params);
    }

    public function expectToSeeCollectionsAmountOf(int $expectedAmount): void
    {
        $elements = $this->grabDataFromResponseByJsonPath('.elements')[0] ?? [];

        $this->assertEquals($expectedAmount, \count($elements));
    }

    public function grantUserAccessToCollection(string $collectionId, string $tokenId, array $roles = null): void
    {
        $payload = ['user' => $tokenId];

        if ($roles !== null) {
            $payload['roles'] = $roles;
        }

        $this->postJson(
            $this->fill(
                Urls::URL_COLLECTION_GRANT_TOKEN,
                ['collection' => $collectionId]
            ),
            $payload
        );
    }

    public function revokeAccessToCollection(string $collectionId, string $tokenId): void
    {
        $this->sendDELETE(
            $this->fill(
                Urls::URL_COLLECTION_REVOKE_TOKEN,
                [
                    'collection' => $collectionId,
                    'user'       => $tokenId
                ]
            )
        );
    }

    public function canSeeResponseOfRevokedAccessIsSuccessful(): void
    {
        $this->canSeeResponseCodeIsSuccessful();
        $this->canSeeResponseContainsJson([
            "message" => "OK",
            "status"  => true,
            'data'   => [
                'user'       => [],
                'collection' => []
            ]
        ]);
    }

    public function canSeeResponseOfGrantedAccessIsSuccessful(): void
    {
        // it is the same
        $this->canSeeResponseOfRevokedAccessIsSuccessful();
    }

    public function canSeeResponseCannotGrantAccessToCollection(): void
    {
        $this->canSeeErrorResponse(
            'No permissions to grant and/or revoke access for other users in this collection',
            40308,
            'request.auth-error'
        );

        $this->canSeeResponseCodeIsClientError();
    }

    public function canSeeResponseCannotGrantTooMuchAccessThanWeHave(): void
    {
        $this->canSeeErrorResponse(
            'Cannot give roles to other user that current context user does not have',
            40315,
            'request.auth-error'
        );

        $this->canSeeResponseCodeIsClientError();
    }

    public function canSeeResponseCannotUploadToCollection(): void
    {
        $this->canSeeResponseContainsJson([
            "error" => "Current access does not grant you a possibility to upload to this backup collection",
            "code"  => 40308,
            "type"  => "request.auth-error"
        ]);

        $this->canSeeResponseCodeIsClientError();
    }

    public function canSeeResponseOfUploadingToCollectionIsSuccessful(): void
    {
        $this->canSeeResponseContainsJson([
            'message'    => "File was uploaded",
            'status'     => true,
            'version'    => [],
            'collection' => []
        ]);

        $this->canSeeResponseCodeIsSuccessful();
    }

    public function canSeeErrorResponse(string $error, int $code, string $type): void
    {
        $this->canSeeResponseContainsJson([
            "error" => $error,
            "code"  => $code,
            "type"  => $type
        ]);
    }

    public function amCollectionManager(): void
    {
        $this->amAdmin();
        $this->haveRoles([
            'upload.all',
            'collections.create_new',
            'collections.manage_users_in_allowed_collections',
            'collections.upload_to_allowed_collections',
            'collections.list_versions_for_allowed_collections'
        ]);
    }
}


class User
{
    public string $id;
    public string $email;
    public string $password;

    public function __construct(string $id, string $email, string $password)
    {
        $this->id       = $id;
        $this->email    = $email;
        $this->password = $password;
    }
}
