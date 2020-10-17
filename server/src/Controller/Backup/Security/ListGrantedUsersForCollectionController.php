<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Security\ListGrantedUsersForCollectionHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\CollectionTokenListingForm;
use App\Infrastructure\Backup\Form\Collection\CollectionTokenListingFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class ListGrantedUsersForCollectionController extends BaseController
{
    private ListGrantedUsersForCollectionHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(ListGrantedUsersForCollectionHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Lists all allowed tokens in given collection
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Collection id, eg. 946348f2-8f3c-4cf0-8827-650fb044ed39"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Lists all users that are granted access to given collection. Notice: Returns censored token ids, when requester has enabled restriction role 'security.cannot_see_full_token_ids'",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="status",
     *             type="boolean",
     *             example=true
     *         ),
     *         @SWG\Property(
     *             property="error_code",
     *             type="integer",
     *             example=0
     *         ),
     *         @SWG\Property(
     *             property="http_code",
     *             type="integer",
     *             example=200
     *         ),
     *         @SWG\Property(
     *             property="errors",
     *             type="array",
     *             @SWG\Items(type="string")
     *         ),
     *         @SWG\Property(
     *             property="tokens",
     *             type="array",
     *             @SWG\Items(ref=@Model(type=\App\Domain\Backup\Entity\Docs\Token::class))
     *         )
     *     )
     * )
     *
     * @param string $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function listTokensAction(string $id): Response
    {
        $form = $this->decodeRequestIntoDTO(['collection' => $id], CollectionTokenListingForm::class);

        $response = $this->handler->handle(
            $form,
            $this->authFactory->createCollectionManagementContext($this->getLoggedUser())
        );

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
