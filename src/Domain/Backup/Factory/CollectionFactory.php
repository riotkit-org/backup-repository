<?php declare(strict_types=1);

namespace App\Domain\Backup\Factory;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValueObjectException;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\ValueObject\BackupStrategy;
use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;
use App\Domain\Backup\ValueObject\Collection\Description;

class CollectionFactory
{
    /**W
     * @param CreationForm $form
     * @return BackupCollection
     *
     * @throws \Exception
     * @throws CollectionMappingError
     */
    public function createFromForm(CreationForm $form): BackupCollection
    {
        $collection = new BackupCollection();

        $mappingErrors = [];
        $mappers = [
            'maxBackupsCount'   => function () use (&$collection, $form) {
                $collection = $collection->withMaxBackupsCount(new CollectionLength($form->maxBackupsCount));
            },
            'maxOneVersionSize' => function () use (&$collection, $form) {
                $collection = $collection->withOneVersionSize(new BackupSize($form->maxOneVersionSize));
            },
            'maxCollectionSize' => function () use (&$collection, $form) {
                $collection = $collection->withCollectionSize(new CollectionSize($form->maxCollectionSize));
            },
            'strategy'          => function () use (&$collection, $form) {
                $collection = $collection->withStrategy(new BackupStrategy($form->strategy));
            },

            'description'       => function () use (&$collection, $form) {
                $collection = $collection->withDescription(new Description((string) $form->description));
            }
        ];

        foreach ($mappers as $formField => $mapper) {
            try {
                $mapper();

            } catch (ValueObjectException $exception) {
                $mappingErrors[$formField] = $exception->getMessage();

            } catch (\TypeError $exception) {
                preg_match('/the type ([a-z]+),/', $exception->getMessage(), $matches);
                $mappingErrors[$formField] = $matches ? 'expected_' . $matches[1] . '_type' : 'invalid_format';
            }
        }

        if ($mappingErrors) {
            throw CollectionMappingError::createFromErrors($mappingErrors);
        }

        return $collection;
    }
}
