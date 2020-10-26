<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\FetchHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Version\FetchVersionForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FetchController extends BaseController
{
    private FetchHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(FetchHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Download selected version of file in the collection
     *
     * @param Request $request
     * @param string  $collectionId
     * @param string  $backupId
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $collectionId, string $backupId): Response
    {
        $requestData = [
            'collection' => $collectionId,
            'versionId'  => $backupId,
            'password'   => (string) $request->get('password')
        ];

        /**
         * @var FetchVersionForm $form
         */
        $form = $this->decodeRequestIntoDTO($requestData, FetchVersionForm::class);

        // insert token as input, so the domain can pass it to the redirect
        $form->token = $this->getLoggedUser()->getId();

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);

        $response = $this->handler->handle(
            $form,
            $this->authFactory->createVersioningContext($user, $form->collection)
        );

        if (!$response) {
            throw new NotFoundHttpException();
        }

        if ($response->isSuccess()) {
            return new StreamedResponse($response->getCallback(), $response->getExitCode());
        }

        return new JsonFormattedResponse($response, $response->getExitCode());
    }
}
