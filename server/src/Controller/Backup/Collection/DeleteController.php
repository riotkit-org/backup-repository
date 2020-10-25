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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        if (!$response) {
            throw new NotFoundHttpException();
        }

        if (trim(strtolower((string) $request->query->get('simulate'))) !== 'true') {
            $this->handler->flush();
        }

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
