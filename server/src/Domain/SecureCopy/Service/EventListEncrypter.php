<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Service;

use App\Domain\SecureCopy\Aggregate\CryptoSpecification;
use App\Domain\SecureCopy\Entity\CryptoMap;

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

    public function encryptEntry(array $input, CryptoSpecification $cryptoSpecification, array &$mapping = []): array
    {
        $output = $input;
        $hashed = $this->crypto->hashString($input['type'] . self::SEP . $input['id'], $cryptoSpecification);

        $output['id'] = $hashed;
        $output['form'] = ['encrypted' => $this->crypto->encodeString(json_encode($input['form']), $cryptoSpecification)];

        // as an expected side-effect we log all hashed ids for later persistence by other layer
        $mapping[$hashed] = CryptoMap::create($hashed, $input['id'], $input['type']);

        return $output;
    }
}
