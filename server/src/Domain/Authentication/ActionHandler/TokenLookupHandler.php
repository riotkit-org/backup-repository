<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class TokenLookupHandler
{
    private TokenRepository $repository;

    public function __construct(TokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $tokenStringToLookup
     * @param AuthenticationManagementContext $context
     *
     * @return null|array
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenStringToLookup, AuthenticationManagementContext $context): ?array
    {
        $token = $this->repository->findTokenById($tokenStringToLookup);
        $this->assertHasRights($context);

        if (!$token instanceof Token) {
            return null;
        }

        return [
            'tokenId'       => $token->getId(),
            'expires'       => $token->getExpirationDate()->format('Y-m-d H:i:s'),
            'roles'         => $token->getRoles(),
            'tags'          => $token->getTags(),
            'mimes'         => $token->getAllowedMimeTypes(),
            'max_file_size' => $token->getMaxAllowedFileSize()
        ];
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context): void
    {
        if (!$context->canLookupAnyToken()) {
            throw new AuthenticationException(
                'Current token does not allow to lookup other tokens',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
