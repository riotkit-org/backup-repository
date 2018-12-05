<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\CreationHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Infrastructure\Backup\Form\Collection\CreationFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreationController extends BaseController
{
    /**
     * @var CreationHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(CreationHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function createAction(Request $request): Response
    {
        $form = new CreationForm();
        $infrastructureForm = $this->submitFormFromJsonRequest($request, $form, CreationFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createCollectionManagementContext($this->getLoggedUserToken())
                );

                if ($request->query->get('simulate') !== 'true') {
                    $this->handler->flush();
                }

                return new JsonResponse($response, $response->getHttpCode());
            }
        );
    }
}
