<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Service;

use App\Domain\SecureCopy\Entity\CryptoMap;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;

/**
 * Encrypts SubmitData type objects
 * Only secret fields are encrypted, the rest stays as is
 */
class EventListEncrypter
{
    private CryptoService $crypto;
    private const SEP = '+++';

    public function __construct(CryptoService $crypto)
    {
        $this->crypto = $crypto;
    }

    public function encryptEntry(array $input, EncryptionPassphrase $passphrase, array &$mapping = []): array
    {
        $output = $input;
        $hashed = $this->crypto->hash($input['type'] . self::SEP . $input['id']);

        $output['id'] = $hashed;
        $output['form'] = ['encrypted' => $this->crypto->encode(json_encode($input['form']), $passphrase->getValue())];

        // as an expected side-effect we log all hashed ids for later persistence by other layer
        $mapping[$hashed] = CryptoMap::create($hashed, $input['id'], $input['type']);

        return $output;
    }
}
