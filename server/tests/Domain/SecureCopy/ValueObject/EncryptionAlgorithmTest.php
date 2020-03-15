<?php declare(strict_types=1);

namespace Tests\Domain\SecureCopy\ValueObject;

use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\Cryptography;
use Tests\BaseTestCase;

class EncryptionAlgorithmTest extends BaseTestCase
{
    /**
     * @see EncryptionAlgorithm::generateInitializationVector()
     */
    public function testGenerateInitializationVector()
    {
        $alogs = ['aes-256-cbc'];

        foreach ($alogs as $algorithm) {
            if (!$algorithm) {
                continue;
            }

            $vo = new EncryptionAlgorithm($algorithm);
            $this->assertNotEmpty($vo->generateInitializationVector(), 'IV length must be `!= 0` for ' . $algorithm);
        }
    }

    /**
     * @see EncryptionAlgorithm::isEncrypting()
     */
    public function testIsEncrypting()
    {
        foreach (Cryptography::CRYPTO_ALGORITHMS as $algorithm) {
            if (!$algorithm) {
                continue;
            }

            $vo = new EncryptionAlgorithm($algorithm);
            $this->assertTrue($vo->isEncrypting());
        }

        $vo = new EncryptionAlgorithm('');
        $this->assertFalse($vo->isEncrypting());
    }
}
