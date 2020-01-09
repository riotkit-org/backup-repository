<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Repository;

use App\Domain\Common\Exception\ReadOnlyException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

abstract class BaseRepository extends ServiceEntityRepository
{
    private $readOnly = false;

    /**
     * @var EntityManager
     */
    private $emProxy;

    public function __construct(ManagerRegistry $registry, string $entityClass, bool $readOnly)
    {
        $this->readOnly = $readOnly;

        parent::__construct($registry, $entityClass);
    }

    /**
     * Returns a proxied ORM that can have disabled write methods in read-only mode
     */
    public function getEntityManager()
    {
        if ($this->emProxy) {
            return $this->emProxy;
        }

        $em = parent::getEntityManager();

        return $this->emProxy = new class($em, $this->readOnly) {
            private const WRITE_METHODS = ['persist', 'flush', 'merge', 'remove'];

            /**
             * @var EntityManager
             */
            private $em;

            /**
             * @var bool
             */
            private $ro;

            public function __construct(EntityManager $em, bool $readOnly)
            {
                $this->em = $em;
                $this->ro = $readOnly;
            }

            public function __get($name)
            {
                return $this->em->$name;
            }

            public function __set($name, $value)
            {
                return $this->em->$name = $value;
            }

            public function __isset($name)
            {
                return isset($this->em->$name);
            }

            public function __call($name, $arguments)
            {
                if ($this->ro && \in_array($name, self::WRITE_METHODS)) {
                    throw new ReadOnlyException('The ORM is read-only');
                }

                return $this->em->$name(...$arguments);
            }
        };
    }
}
