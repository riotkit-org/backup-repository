<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Manager;

use App\Domain\SecureCopy\Repository\CryptoMapRepository;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SecureCopy\Service\EventListEncrypter;

/**
 * Wrapper for the EventListEncrypter, adds additional logic of conditionals and calls the persistence layer
 */
class EncryptionManager
{
    private EventListEncrypter  $encrypter;
    private CryptoMapRepository $idMappingRepository;

    public function __construct(EventListEncrypter $encrypter, CryptoMapRepository $repository)
    {
        $this->encrypter           = $encrypter;
        $this->idMappingRepository = $repository;
    }

    public function encryptSubmitData(array $input, MirroringContext $ctx): array
    {
        if ($ctx->isEncryptionActive()) {
            $mapping = [];
            $out = $this->encrypter->encryptEntry($input, $ctx->getCryptographySpecification(), $mapping);
            $this->idMappingRepository->persist($mapping);

            return $out;
        }

        return $input;
    }

    public function flushAll(): void
    {
        $this->idMappingRepository->flushAll();
    }
}
