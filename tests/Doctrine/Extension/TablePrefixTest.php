<?php declare(strict_types=1);

namespace Tests\Doctrine\Extension;

use Doctrine\Extension\TablePrefix;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Tests\WolnosciowiecTestCase;

/**
 * Tests prefixing for main table and for relations
 *
 * @see TablePrefix
 */
class TablePrefixTest extends WolnosciowiecTestCase
{
    public function testLoadClassMetadata()
    {
        /**
         * @var ClassMetadataInfo|\PHPUnit_Framework_MockObject_MockObject $metadata
         * @var LoadClassMetadataEventArgs|\PHPUnit_Framework_MockObject_MockObject $args
         */
        list($args, $metadata) = $this->getPreparedDependencies();

        $metadata->expects($this->once())
            ->method('setPrimaryTable')
            ->with([
                'name' => 'wolnosciowiec_posts',
            ]);

        $tablePrefix = new TablePrefix('wolnosciowiec_');
        $tablePrefix->loadClassMetadata($args);

        $this->assertSame('wolnosciowiec_tags', $metadata->associationMappings['tags']['joinTable']['name']);
    }

    protected function getPreparedDependencies()
    {
        $metadata = $this->createMock(ClassMetadataInfo::class);
        $metadata->method('getTableName')
            ->willReturn('posts');

        $metadata->method('isInheritanceTypeSingleTable')
            ->willReturn(false);

        $metadata->method('getAssociationMappings')
            ->willReturn([
                'tags' => [
                    'type' => ClassMetadataInfo::MANY_TO_MANY,
                    'isOwningSide' => true,
                    'joinTable' => [
                        'name' => 'tags',
                    ]
                ]
            ]);

        $args = $this->createMock(LoadClassMetadataEventArgs::class);
        $args->method('getClassMetadata')
            ->willReturn($metadata);

        return [
            $args,
            $metadata,
        ];
    }
}
