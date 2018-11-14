<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Repository\TokenRepository;

class TokenLookupHandler
{
    /**
     * @var TokenRepository
     */
    private $repository;

    /**
     * @var SecurityContextFactory
     */
    private $security;

    public function __construct(TokenRepository $repository, SecurityContextFactory $security)
    {
        $this->repository = $repository;
        $this->security   = $security;
    }

    /**
     * @param string $tokenStringToLookup
     * @param Token $currentUserToken
     *
     * @return null|array
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenStringToLookup, Token $currentUserToken): ?array
    {
        $token = $this->repository->findTokenById($tokenStringToLookup);
        $securityContext = $this->security->createFromToken($currentUserToken);

        if (!$securityContext->canLookupAnyToken()) {
            throw new AuthenticationException(
                'Current token does not allow to lookup other tokens',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        if (!$token instanceof Token) {
            return null;
        }

        return [
            'tokenId' => $token->getId(),
            'expires' => $token->getExpirationDate()->format('Y-m-d H:i:s'),
            'roles'   => $token->getRoles(),
            'tags'    => $token->getTags(),
            'mimes'   => $token->getAllowedMimeTypes()
        ];
    }
}
