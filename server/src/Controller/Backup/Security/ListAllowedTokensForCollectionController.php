<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Security\ListAllowedTokensForCollectionHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\CollectionTokenListingForm;
use App\Infrastructure\Backup\Form\Collection\CollectionTokenListingFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListAllowedTokensForCollectionController extends BaseController
{
    private ListAllowedTokensForCollectionHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(ListAllowedTokensForCollectionHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * todo: swagger docs
     *
     * @param Request $request
     * @param string $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function listTokensAction(Request $request, string $id): Response
    {
        $form = new CollectionTokenListingForm();
        $infrastructureForm = $this->createForm(CollectionTokenListingFormType::class, $form);
        $infrastructureForm->submit([
            'collection' => $id,
        ]);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createCollectionManagementContext($this->getLoggedUserToken())
                );

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }
}
