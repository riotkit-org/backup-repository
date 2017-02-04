<?php declare(strict_types=1);

namespace Repository;

use Doctrine\ORM\EntityManager;
use Model\Entity\Token;
use Repository\Domain\TokenRepositoryInterface;

/**
 * @package Repository
 */
class TokenRepository implements TokenRepositoryInterface
{
    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @inheritdoc
     */
    public function getTokenById($tokenId)
    {
        return $this->em->find(Token::class, $tokenId);
    }

    /**
     * @inheritdoc
     */
    public function getExpiredTokens(): array
    {
        return $this->em->getRepository(Token::class)
            ->createQueryBuilder('t')
            ->select()
            ->where('t.expirationDate >= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }
}