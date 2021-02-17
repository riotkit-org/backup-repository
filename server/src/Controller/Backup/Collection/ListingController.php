<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\ListingHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\ListingForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListingController extends BaseController
{

    private ListingHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        ListingHandler         $handler,
        SecurityContextFactory $authFactory
    ) {
        $this->handler       = $handler;
        $this->authFactory   = $authFactory;
    }

    /**
     * Search for collections
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handleAction(Request $request): Response
    {
        /**
         * @var ListingForm $form
         */
        $form = $this->decodeRequestIntoDTO($request->query->all(), ListingForm::class);

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);

        $securityContext = $this->authFactory->createCollectionManagementContext($user, null);
        $response = $this->handler->handle($form, $securityContext);

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
