<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\DeleteHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class DeleteController extends BaseController
{
    private DeleteHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        DeleteHandler $editHandler,
        SecurityContextFactory $authFactory
    ) {
        $this->handler       = $editHandler;
        $this->authFactory   = $authFactory;
    }

    /**
     * Delete a collection
     *
     * @SWG\Parameter(
     *     type="string",
     *     in="path",
     *     name="id",
     *     description="Collection id"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Collection was deleted",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="status",
     *             type="boolean",
     *             example="true"
     *         ),
     *         @SWG\Property(
     *             property="http_code",
     *             type="integer",
     *             example="200"
     *         ),
     *         @SWG\Property(
     *             property="error_code",
     *             type="integer",
     *             example="50091"
     *         ),
     *         @SWG\Property(
     *             property="errors",
     *             type="array",
     *             @SWG\Items(type="string")
     *         ),
     *         @SWG\Property(
     *             property="message",
     *             type="string",
     *             example="OK, collection deleted"
     *         ),
     *         @SWG\Property(
     *             property="collection",
     *             ref=@Model(type=\App\Domain\Backup\Entity\Docs\Collection::class)
     *         ),
     *          @SWG\Property(
     *             property="context",
     *             type="array",
     *             @SWG\Items(type="string")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param string  $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $id): Response
    {
        /**
         * @var DeleteForm $form
         */
        $form = $this->decodeRequestIntoDTO(['collection' => $id], DeleteForm::class);

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);

        $securityContext = $this->authFactory->createCollectionManagementContext($user, $form->collection);
        $response = $this->handler->handle($form, $securityContext);

        if (trim(strtolower((string) $request->query->get('simulate'))) !== 'true') {
            $this->handler->flush();
        }

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
