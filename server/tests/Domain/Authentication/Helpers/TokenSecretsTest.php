<?php declare(strict_types=1);

namespace Tests\Domain\Authentication\Entity;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Helper\TokenSecrets;
use App\Infrastructure\Authentication\Repository\TokenDoctrineRepository;
use Doctrine\ORM\AbstractQuery;
use Ramsey\Uuid\Uuid;
use Tests\BaseTestCase;

class TokenSecretsTest extends BaseTestCase
{
    /**
     * @see TokenSecrets::generateDQLConcatString()
     * @see TokenSecrets::getStrippedOutToken()
     */
    public function testGenerateDQLConcatString()
    {
        $uuid = '16f63f40-0087-467c-bebb-5c6b82e8e8c0';
        $generated = TokenSecrets::generateDQLConcatString('\'' . $uuid . '\'');
        $strippedInPHP = TokenSecrets::getStrippedOutToken($uuid);

        // prepare Symfony DI
        self::bootKernel();
        $repository = self::$container->get(TokenDoctrineRepository::class);

        // we need to have any data in the table in order to execute any query...
        // even if we want to only execute something like SELECT 1. The Doctrine does not allow to delete "FROM".
        $this->populateDatabaseWithExampleToken($repository);

        // pass our DQL through Doctrine to see if it is correct
        $qb = $repository->createQueryBuilder('t');
        $qb->select($generated);

        $parsedResult = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR);

        // assertions
        $this->assertSame($strippedInPHP, $parsedResult[0][1], 'UUIDv4 should be "censored" same way in Doctrine as in PHP');
        $this->assertSame(strlen($uuid), strlen($parsedResult[0][1]), 'Censored UUIDv4 and original UUIDv4 should have same length');
        $this->assertSame('*****f40-**87-**7c-**bb-********e8c0', $parsedResult[0][1], 'The result should be predictable');
    }

    private function populateDatabaseWithExampleToken(TokenDoctrineRepository $repository): void
    {
        $testToken = new Token();
        $testToken->setId(Uuid::uuid4()->toString());
        $repository->persist($testToken);
        $repository->flush();
    }
}
