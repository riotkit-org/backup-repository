<?php declare(strict_types=1);

namespace Tests\Domain\Authentication\Entity;

use App\Domain\Authentication\Helper\TokenSecrets;
use App\Infrastructure\Authentication\Repository\TokenDoctrineRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Cache;
use Symfony\Component\Cache\Adapter\AdapterInterface;
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

        $cache = self::$container->get(AdapterInterface::class);
        dump(get_class($cache));

        dump($cache->get('hehe', function () { return date('Y-m-d H:i:s'); }));
        die();

        // pass our DQL through Doctrine to see if it is correct
        $qb = $repository->createQueryBuilder('t');
        $qb->select($generated);
        $parsedResult = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR);

        // assertions
        $this->assertSame($strippedInPHP, $parsedResult[0][1], 'UUIDv4 should be "censored" same way in Doctrine as in PHP');
        $this->assertSame(strlen($uuid), strlen($parsedResult[0][1]), 'Censored UUIDv4 and original UUIDv4 should have same length');
        $this->assertSame('*****f40-**87-**7c-**bb-********e8c0', $parsedResult[0][1], 'The result should be predictable');
    }
}
