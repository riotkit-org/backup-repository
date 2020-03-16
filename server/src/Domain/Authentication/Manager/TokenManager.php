<?php declare(strict_types=1);

namespace App\Domain\Authentication\Manager;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\InvalidTokenIdException;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Service\CryptoService;
use App\Domain\Authentication\Service\UuidValidator;

class TokenManager
{
    private TokenRepository $repository;
    private UuidValidator $uuidValidator;
    private CryptoService $cryptoService;

    public function __construct(TokenRepository $repository, UuidValidator $uuidValidator, CryptoService $crypto)
    {
        $this->repository    = $repository;
        $this->uuidValidator = $uuidValidator;
        $this->cryptoService = $crypto;
    }

    /**
     * @param array $roles
     * @param \DateTimeImmutable $expirationTime
     * @param array $details
     * @param string|null $customId
     *
     * @return Token
     *
     * @throws InvalidTokenIdException
     * @throws \App\Domain\Authentication\Exception\MissingDataFieldsError
     */
    public function generateNewToken(array $roles, \DateTimeImmutable $expirationTime,
                                     array $details, ?string $customId = null): Token
    {
        $token = new Token();
        $token->setId($customId ?: uniqid('', true));
        $token->setRoles($roles);
        $token->setExpirationDate($expirationTime);
        $token->setData($this->processTokenData($details));

        $this->repository->persist($token);

        if ($customId) {
            if (!$this->uuidValidator->isValid($customId)) {
                throw new InvalidTokenIdException('Invalid token id format. Expected UUIDv4 format');
            }

            $token->setId($customId);
            $this->repository->persist($token);
        }

        return $token;
    }

    public function revokeToken(Token $token): void
    {
        $this->repository->remove($token);
    }

    public function flushAll(): void
    {
        $this->repository->flush();
    }

    private function processTokenData(array $data): array
    {
        // encrypt the key with master key, as it should not be visible to the user
        if ($data[Token::FIELD_SECURE_COPY_ENC_KEY] ?? '') {
            $data[Token::FIELD_SECURE_COPY_ENC_KEY] = $this->cryptoService->encodeString($data[Token::FIELD_SECURE_COPY_ENC_KEY]);
        }

        // the same for digest salt
        if ($data[Token::FIELD_SECURE_COPY_DIGEST_SALT] ?? '') {
            $data[Token::FIELD_SECURE_COPY_DIGEST_SALT] = $this->cryptoService->encodeString($data[Token::FIELD_SECURE_COPY_DIGEST_SALT]);
        }

        return $data;
    }
}
