<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\AccessTokenListingHandler;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AccessTokenListingForm;
use App\Domain\Common\Exception\CommonValueException;
use App\Infrastructure\Common\Exception\JsonRequestException;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenListingController extends BaseController
{
    private AccessTokenListingHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(AccessTokenListingHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws AuthenticationException
     * @throws CommonValueException
     * @throws JsonRequestException
     */
    public function listAction(Request $request): Response
    {
        /**
         * @var User $currentUser
         */
        $currentUser = $this->getLoggedUser(User::class);

        /**
         * @var AccessTokenListingForm $form
         */
        $form = $this->decodeRequestIntoDTO($request->query->all(), AccessTokenListingForm::class);

        return new JsonFormattedResponse(
            $this->handler->handle(
                $this->authFactory->createFromUserAccount($currentUser),
                $form
            ),
            JsonFormattedResponse::HTTP_OK
        );
    }
}
