<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Security\ListGrantedUsersForCollectionHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\CollectionTokenListingForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param string $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function listTokensAction(string $id): Response
    {
        /**
         * @var CollectionTokenListingForm $form
         */
        $form = $this->decodeRequestIntoDTO(['collection' => $id], CollectionTokenListingForm::class);

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);

        $response = $this->handler->handle(
            $form,
            $this->authFactory->createCollectionManagementContext($user, $form->collection)
        );

        if (!$response) {
            throw new NotFoundHttpException();
        }

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
