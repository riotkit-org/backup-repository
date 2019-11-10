<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\ListingHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\ListingForm;
use App\Infrastructure\Backup\Form\Collection\ListingFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListingController extends BaseController
{
    /**
     * @var ListingHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(
        ListingHandler         $handler,
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
    public function handleAction(Request $request): Response
    {
        $form = new ListingForm();
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, ListingFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                $securityContext = $this->authFactory->createCollectionManagementContext(
                    $this->getLoggedUserToken()
                );

                $response = $this->handler->handle($form, $securityContext);

                return new JsonResponse($response, $response->getHttpCode());
            }
        );
    }
}
