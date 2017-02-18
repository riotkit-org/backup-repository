<?php declare(strict_types=1);

namespace Manager;

use Doctrine\ORM\EntityManager;
use Factory\TokenFactory;
use Manager\Domain\TokenManagerInterface;
use Model\Entity\Token;
use Repository\Domain\TokenRepositoryInterface;

/**
 * @package Manager
 */
class TokenManager implements TokenManagerInterface
{
    /**
     * @var TokenRepositoryInterface $repository
     */
    private $repository;

    /**
     * @var string $adminToken
     */
    private $adminToken;

    /**
     * @var TokenFactory $factory
     */
    private $factory;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @param TokenRepositoryInterface $repository
     * @param TokenFactory $factory
     * @param EntityManager $entityManager
     * @param string $adminToken
     */
    public function __construct(
        TokenRepositoryInterface $repository,
        TokenFactory $factory,
        EntityManager $entityManager,
        string $adminToken
    ) {
        $this->repository    = $repository;
        $this->factory       = $factory;
        $this->entityManager = $entityManager;
        $this->adminToken    = $adminToken;
    }

    /**
     * @inheritdoc
     */
    public function isTokenValid(string $tokenId, array $requiredRoles = []): bool
    {
        if ($tokenId === $this->getAdminToken()) {
            return true;
        }

        $token = $this->repository->getTokenById($tokenId);

        if (!$token instanceof Token || empty($requiredRoles)) {
            return false;
        }

        // at least one role must match
        foreach ($requiredRoles as $roleName) {

            if ($token->hasRole($roleName) && $token->isNotExpired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isAdminToken(string $tokenId): bool
    {
        return $this->getAdminToken() === $tokenId;
    }

    /**
     * @inheritdoc
     */
    public function generateNewToken(array $roles, \DateTime $expires, array $data = []): Token
    {
        $token = $this->factory->createNewToken($roles, $expires, $data);

        $this->entityManager->persist($token);
        $this->entityManager->flush($token);

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function removeToken(Token $token)
    {
        $this->entityManager->remove($token);
        $this->entityManager->flush($token);
    }

    /**
     * @return string
     */
    public function getAdminToken(): string
    {
        return $this->adminToken;
    }
}