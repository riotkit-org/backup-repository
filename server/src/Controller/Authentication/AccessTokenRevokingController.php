<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\AccessTokenRevokingHandler;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AccessTokenRevokingForm;
use App\Domain\Authentication\Service\Security\HashEncoder;
use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Common\Exception\ResourceNotFoundException;
use App\Infrastructure\Common\Exception\JsonRequestException;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenRevokingController extends BaseController
{
    private AccessTokenRevokingHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(AccessTokenRevokingHandler $handler, SecurityContextFactory $authFactory)
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
     * @throws ResourceNotFoundException
     */
    public function revokeAction(Request $request): Response
    {
        /**
         * @var User $currentUser
         */
        $currentUser = $this->getLoggedUser(User::class);

        /**
         * @var AccessTokenRevokingForm $form
         */
        $form = $this->decodeRequestIntoDTO($request->query->all(), AccessTokenRevokingForm::class);
        $form->currentSessionTokenHash = HashEncoder::encode($this->getCurrentSessionToken($request));

        return new JsonFormattedResponse(
            $this->handler->handle(
                $this->authFactory->createFromUserAccount($currentUser),
                $form
            ),
            JsonFormattedResponse::HTTP_OK
        );
    }
}
