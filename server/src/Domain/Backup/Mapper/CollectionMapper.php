<?php declare(strict_types=1);

namespace App\Domain\Backup\Mapper;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\ValueObjectException;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Repository\UserRepository;
use App\Domain\Backup\ValueObject\BackupStrategy;
use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;
use App\Domain\Backup\ValueObject\Collection\Description;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\Password;
use App\Domain\Common\Exception\CommonStorageException;
use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Common\Exception\DomainAssertionFailure;
use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;
use Psr\Log\LoggerInterface;

class CollectionMapper
{
    public function __construct(private UserRepository $tokenRepository, private LoggerInterface $logger)
    {
    }

    /**
     * Maps FORM into internal DTO and ValueObjects
     *
     * @param CreationForm     $form
     * @param BackupCollection $collection
     *
     * @return BackupCollection
     *
     * @throws \Exception
     * @throws DomainAssertionFailure
     */
    public function mapFormIntoCollection(CreationForm $form, BackupCollection $collection): BackupCollection
    {
        $mappingErrors = [];
        $mappers = $this->getMappers($collection, $form);

        foreach ($mappers as $formField => $mapper) {
            try {
                $mapper();

            } catch (DomainInputValidationConstraintViolatedError $exception) {
                $mappingErrors[] = $exception;

            } catch (CommonValueException | CommonStorageException | ValueObjectException $exception) {
                $mappingErrors[] = $mappingErrors[] = DomainInputValidationConstraintViolatedError::fromString(
                    $formField,
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception
                );

            // generic typing errors
            } catch (\TypeError $exception) {
                preg_match('/the type ([a-z]+),/', $exception->getMessage(), $matches);

                $this->logger->debug('Validation exception of generic type', ['exc' => $exception]);

                $mappingErrors[] = DomainInputValidationConstraintViolatedError::fromString(
                    $formField,
                    Errors::ERR_MSG_REQUEST_INPUT_GENERIC_INVALID_FORMAT,
                    Errors::ERR_REQUEST_INPUT_GENERIC_INVALID_FORMAT,
                    $exception
                );
            }
        }

        if ($mappingErrors) {
            throw DomainAssertionFailure::fromErrors($mappingErrors);
        }

        return $collection;
    }

    /**
     * @param BackupCollection $collection The reference needs to be there. PHP is loosing the reference without it.
     * @param CreationForm $form
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
                $collection = $collection->withDescription(Description::fromString((string) $form->description));
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
