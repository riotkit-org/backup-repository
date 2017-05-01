<?php declare(strict_types=1);

namespace Doctrine\Extension;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Based on official documentation
 * -------------------------------
 *   Allows to add prefixes to all tables managed by Doctrine ORM
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/stable/cookbook/sql-table-prefixes.html
 */
class TablePrefix
{
    protected $prefix = '';

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /* @var $classMetadata \Doctrine\ORM\Mapping\ClassMetadataInfo */
        $classMetadata = $eventArgs->getClassMetadata();

        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $classMetadata->setPrimaryTable(array(
                'name' => $this->prefix . $classMetadata->getTableName()
            ));
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
            }
        }
    }
}
