<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Service\TokenDecryptionService;

class IncomingTokenFactory
{
    /**
     * @var TokenDecryptionService
     */
    private $decryptionService;

    public function __construct(TokenDecryptionService $decryptionService, TokenRepository $repository)
    {
        $this->decryptionService = $decryptionService;
        $this->repository        = $repository;
    }

    /**
     * @param string $tokenString
     *
     * @return Token
     *
     * @throws AuthenticationException
     */
    public function createFromEncodedString(string $tokenString): Token
    {
        $decrypted = $this->decryptionService->decode($tokenString);
        $decoded = \json_decode($decrypted, true);

        if (\is_array($decoded)) {
            return $this->createTokenFromArray($decoded);
        }

        $persistedToken = $this->repository->findTokenById($decrypted);

        if (!$persistedToken) {
            throw new AuthenticationException(
                'Invalid token, cannot decrypt, parse or find token id in the persistent database',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        return $persistedToken;
    }

    /**
     * @param array $input
     *
     * @return Token
     *
     * @throws AuthenticationException
     * @throws \Exception
     */
    private function createTokenFromArray(array $input): Token
    {
        $this->assertHasAllRequiredFields($input);

        $token = new Token();
        $token->setData($input['data'] ?? []);
        $token->setExpirationDate(new \DateTimeImmutable($input['expirationDate']));
        $token->setRoles($input['roles'] ?? []);
        $token->setCreationDate(new \DateTimeImmutable());

        return $token;
    }

    /**
     * @param array $input
     *
     * @throws AuthenticationException
     */
    private function assertHasAllRequiredFields(array $input): void
    {
        foreach (Token::REQUIRED_FIELDS as $field => $type) {
            if (!isset($input[$field]) || \gettype($input[$field]) !== $type) {
                throw new AuthenticationException(
                    'Token has corrupted structure. Requirements: ' . \json_encode(Token::REQUIRED_FIELDS),
                    AuthenticationException::CODES['token_invalid']
                );
            }
        }
    }
}
