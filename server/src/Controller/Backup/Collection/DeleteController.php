<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\DeleteHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Infrastructure\Backup\Form\Collection\DeleteFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Request $request
     * @param string  $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $id): Response
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
            function () use ($form, $request) {
                $securityContext = $this->authFactory->createCollectionManagementContext(
                    $this->getLoggedUserToken()
                );

                $response = $this->handler->handle($form, $securityContext);

                if ($request->query->get('simulate') !== 'true') {
                    $this->handler->flush();
                }

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }
}
