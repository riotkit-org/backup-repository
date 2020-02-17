<?php declare(strict_types=1);

namespace Tests\Domain\SecureCopy\ValueObject;

use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SSLAlgorithms;
use Tests\BaseTestCase;

class EncryptionAlgorithmTest extends BaseTestCase
{
    /**
     * @see EncryptionAlgorithm::generateInitializationVector()
     */
    public function testGenerateInitializationVector()
    {
        $alogs = [
            'aes-128-cbc', 'aes-128-cfb', 'aes-128-cfb1', 'aes-256-cbc',
            'aes-256-ctr', 'des3', 'blowfish'
        ];

        foreach ($alogs as $algorithm) {
            if (!$algorithm) {
                continue;
            }

            $vo = new EncryptionAlgorithm($algorithm);
            $this->assertNotEmpty($vo->generateInitializationVector(), 'IV length must be `!= 0` for ' . $algorithm);
        }

        // non-IV algorithms
        $vo = new EncryptionAlgorithm('aes-256-ecb');
        $this->assertEmpty($vo->generateInitializationVector(), 'IV is not required by ECB-type cipher');
    }

    /**
     * @see EncryptionAlgorithm::isEncrypting()
     */
    public function testIsEncrypting()
    {
        foreach (SSLAlgorithms::ALGORITHMS as $algorithm) {
            if (!$algorithm) {
                continue;
            }

            $vo = new EncryptionAlgorithm($algorithm);
            $this->assertTrue($vo->isEncrypting());
        }
    }
}
