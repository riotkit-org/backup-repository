<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GrantTokenToCollectionController extends ManageAllowedTokensController
{
    /**
     * @SWG\Post(
     *     description="Request to grant access to collection for given token",
     *     consumes={"application/json"},
     *     produces={"applicattion/json"},
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
     *         in="body",
     *         name="body",
     *         description="JSON payload",
     *
     *         @SWG\Schema(
     *             type="object",
     *             required={"token"},
     *             @SWG\Property(property="token", example="ca6a2635-d2cb-4682-ba81-3879dd0e8a77", type="string"),
     *         )
     *     ),
     *
     *     @SWG\Response(
     *         response="200",
     *         description="Access to collection was granted or revoked",
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
     *                     property="token",
     *                     ref=@Model(type=\App\Domain\Backup\Entity\Docs\Token::class)
     *                 ),
     *
     *                 @SWG\Property(
     *                     property="tokens_count",
     *                     type="integer",
     *                     example=5
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param string $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $id): Response
    {
        return parent::handleAction($request, $id);
    }
}
