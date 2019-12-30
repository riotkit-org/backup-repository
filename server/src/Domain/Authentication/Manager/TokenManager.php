<?php declare(strict_types=1);

namespace App\Domain\Authentication\Manager;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\InvalidTokenIdException;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Service\CryptoService;
use App\Domain\Authentication\Service\UuidValidator;
use Exception;
use Ramsey\Uuid\Uuid;

class TokenManager
{
    /**
     * @var TokenRepository
     */
    private $repository;

    /**
     * @var UuidValidator
     */
    private $uuidValidator;

    /**
     * @var CryptoService
     */
    private $cryptoService;

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
     *
     * @return Token
     * @throws Exception
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
        if ($data[Token::FIELD_REPLICATION_ENC_KEY] ?? '') {
            $data[Token::FIELD_REPLICATION_ENC_KEY] = $this->cryptoService->encode($data[Token::FIELD_REPLICATION_ENC_KEY]);
        }

        return $data;
    }
}
