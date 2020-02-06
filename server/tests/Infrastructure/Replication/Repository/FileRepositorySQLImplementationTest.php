<?php declare(strict_types=1);

namespace Tests\Infrastructure\Replication\Repository;

use App\Infrastructure\SecureCopy\Repository\FileRepositorySQLImplementation;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\FunctionalTestCase;
use Tests\RestoreDbBetweenTestsTrait;

class FileRepositorySQLImplementationTest extends FunctionalTestCase
{
    use RestoreDbBetweenTestsTrait;

    public function provideDataForFinding(): array
    {
        return [
            'Since stone age era' => [
                'since'         => new \DateTime('1900-01-01'),
                'expectedCount' => 1
            ],
            'No since time specified' => [
                'since'         => null,
                'expectedCount' => 1
            ],
            'Since is in the future' => [
                'since'         => new \DateTime('2050-01-01'),
                'expectedCount' => 0
            ]
        ];
    }

    /**
     * @dataProvider provideDataForFinding
     *
     * @throws DBALException
     */
    public function testFindFilesToReplicateSince(?\DateTime $sice, int $expectedCount)
    {
        $client = $this->createClient();
        $this->prepareData();

        /**
         * @var FileRepositorySQLImplementation $repository
         */
        $repository = $client->getContainer()->get(FileRepositorySQLImplementation::class);

        $results = $repository->findFilesSince($sice);

        $this->assertEquals($expectedCount, $results->count());
    }

    /**
     * Seed te database
     *
     * @throws DBALException
     */
    private function prepareData(): void
    {
        /**
         * @var Connection $connection
         */
        $connection = self::$container->get(Connection::class);

        $connection->executeQuery(
            'INSERT INTO file_registry 
                   (id, fileName, contentHash, dateAdded, mimeType, password, public, timezone)
                   VALUES (161, "antifa.png", "7c0ca491e121cb0c9fa76a53f0c4348ec3f0362237124785ebe6a4267ba737be", 
                   "2019-05-01 08:00:00", "image/png", "", true, "Europe/Warsaw");
        ');
    }
}
