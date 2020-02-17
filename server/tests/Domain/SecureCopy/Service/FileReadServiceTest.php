<?php declare(strict_types=1);

namespace Tests\Domain\SecureCopy\Service;

use App\Domain\SecureCopy\Service\FileReadService;
use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;
use Tests\BaseTestCase;

/**
 * @see FileReadService
 */
class FileReadServiceTest extends BaseTestCase
{
    /**
     * @see FileReadService::getPlainStream()
     * @see FileReadService::createReadCallback()
     */
    public function testCommandNotFoundWithoutCryptoThrowsDetailedException(): void
    {
        $this->expectExceptionMessageRegExp('/non-existing-command: command not found/');

        $testResource = fopen('php://memory', 'wb');

        $service = new FileReadService();
        $this->setProtectedProperty($service, 'consoleBinPath', 'non-existing-command');

        $result = $service->getPlainStream('strike-plan.odt', $testResource);
        $callback = $result->getStreamFlushingCallback();
        $callback();
    }

    public function provideOpenSSLInput(): array
    {
        return [
            'With IV' => [
                'algorithm' => 'aes-128-cbc',
                'iv'        => 'test-iv',
                'decrypt'   => false,
                'expectedCommand' =>
                    'openssl enc -aes-128-cbc -K ' .
                    '"636f72706f726174696f6e732d6172652d6576696c2d626563617573652d746865792d6172652d707269766174697a6' .
                    '96e672d7075626c69632d676f6f64" -iv "test-iv"'
            ],

            'Without IV' => [
                'algorithm' => 'aes-128-ecb',
                'iv'        => '',
                'decrypt'   => false,
                'expectedCommand' =>
                    'openssl enc -aes-128-ecb -K ' .
                    '"636f72706f726174696f6e732d6172652d6576696c2d626563617573652d746865792d6172652d707269766174697a6' .
                    '96e672d7075626c69632d676f6f64"'
            ]
        ];
    }

    /**
     * @dataProvider provideOpenSSLInput
     *
     * @param string $algorithm
     * @param string $iv
     * @param bool $decrypt
     * @param string $expectedCommand
     *
     * @see FileReadService::generateShellCryptoCommand()
     */
    public function testGenerateShellCryptoCommandGeneratesCommandWithIV(string $algorithm, string $iv, bool $decrypt,
                                                                         string $expectedCommand): void
    {
        $service = new FileReadService();
        $command = $service->generateShellCryptoCommand(
            new EncryptionAlgorithm($algorithm),
            new EncryptionPassphrase('corporations-are-evil-because-they-are-privatizing-public-good'),
            $iv,
            $decrypt
        );

        $this->assertSame($expectedCommand, str_replace('  ', ' ', trim($command)));
    }
}
