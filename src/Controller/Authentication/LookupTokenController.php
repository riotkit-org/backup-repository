<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenLookupHandler;
use App\Domain\Authentication\Entity\Token;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LookupTokenController extends BaseController
{
    /**
     * @var TokenLookupHandler
     */
    private $handler;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenLookupHandler $handler, TokenStorageInterface $tokenStorage)
    {
        $this->handler = $handler;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $token
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function handle(string $token): JsonResponse
    {
        return new JsonResponse(
            $this->handler->handle(
                $token,
                $this->getLoggedUserToken()
            ),
            JsonResponse::HTTP_ACCEPTED
        );
    }

    private function getLoggedUserToken(): Token
    {
        /**
         * @var TokenTransport $sessionToken
         */
        $sessionToken = $this->tokenStorage->getToken();

        return $sessionToken->getToken();
    }
}
