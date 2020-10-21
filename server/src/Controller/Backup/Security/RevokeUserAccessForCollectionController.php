<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class RevokeUserAccessForCollectionController extends ManageCollectionAccessControlController
{
    /**
     * Revoke a token from access to collection
     *
     * @SWG\Delete(
     *     description="Request revoke access to the token for given collection",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         type="boolean",
     *         in="query",
     *         name="simulate",
     *         description="If set to true, then no changes are comimited"
     *     ),
     *
     *     @SWG\Parameter(
     *         type="string",
     *         in="path",
     *         name="id",
     *         description="Collection id for which to manage token access"
     *     ),
     *
     *     @SWG\Parameter(
     *         type="string",
     *         in="path",
     *         name="user",
     *         description="User id to revoke access for (subject token)"
     *     ),
     *
     *     @SWG\Response(
     *         response="200",
     *         description="Access to collection was revoked",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @SWG\Property(
     *                 property="http_code",
     *                 type="integer",
     *                 example="200"
     *             ),
     *             @SWG\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @SWG\Property(
     *                     property="collection",
     *                     ref=@Model(type=\App\Domain\Backup\Entity\Docs\Collection::class)
     *                 ),
     *
     *                 @SWG\Property(
     *                     property="uid",
     *                     ref=@Model(type=\App\Domain\Backup\Entity\Docs\Token::class)
     *                 ),
     *
     *                 @SWG\Property(
     *                     property="users_count",
     *                     type="integer",
     *                     example=4
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param string $id
     * @param string $uid
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $id, string $uid = ''): Response
    {
        return parent::handleAction($request, $id, $uid);
    }
}
