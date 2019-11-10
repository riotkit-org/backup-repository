<?php declare(strict_types=1);

namespace App\Domain\Backup\Mapper;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValueObjectException;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Repository\TokenRepository;
use App\Domain\Backup\ValueObject\BackupStrategy;
use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;
use App\Domain\Backup\ValueObject\Collection\Description;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\Password;

class CollectionMapper
{
    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @param CreationForm     $form
     * @param BackupCollection $collection
     *
     * @return BackupCollection
     *
     * @throws \Exception
     * @throws CollectionMappingError
     */
    public function mapFormIntoCollection(CreationForm $form, BackupCollection $collection): BackupCollection
    {
        $mappingErrors = [];
        $mappers = $this->getMappers($collection, $form);

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

    public function mapTokenIntoCollection(BackupCollection $collection, string $tokenId): BackupCollection
    {
        $token = $this->tokenRepository->findTokenById($tokenId);

        if (!$token) {
            return $collection;
        }

        return $collection->withTokenAdded($token);
    }

    /**
     * @param BackupCollection $collection The reference needs to be there. PHP is loosing the reference without it.
     * @param CreationForm $form
     * @param bool $isNewElement
     *
     * @return array
     */
    private function getMappers(BackupCollection &$collection, CreationForm $form): array
    {
        return [
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
            },
            'password'          => function () use (&$collection, $form) {
                $collection = $collection->withPassword(new Password((string) $form->password));
            },
            'filename'          => function () use (&$collection, $form) {
                $collection = $collection->withFilename(new Filename((string) $form->filename));
            }
        ];
    }
}
