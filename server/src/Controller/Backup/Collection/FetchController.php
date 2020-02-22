<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\FetchHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Infrastructure\Backup\Form\Collection\DeleteFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
     * @param string  $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(string $id): Response
    {
        $form = new DeleteForm();
        $infrastructureForm = $this->createForm(DeleteFormType::class, $form);
        $infrastructureForm->submit([
            'collection' => $id
        ]);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                $securityContext = $this->authFactory->createCollectionManagementContext(
                    $this->getLoggedUserToken()
                );

                $response = $this->handler->handle($form, $securityContext);

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }
}
