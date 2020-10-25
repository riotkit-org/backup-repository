<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\FetchHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FetchController extends BaseController
{
    private FetchHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        FetchHandler $handler,
        SecurityContextFactory $authFactory
    ) {
        $this->handler       = $handler;
        $this->authFactory   = $authFactory;
    }

    /**
     * Fetch information about collection
     *
     * @param string  $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(string $id): Response
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

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
