<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

// @todo: Namespace
use App\Domain\Common\Service\CryptoService;
use App\Domain\Common\DTO\CryptoStream;
use App\Domain\Common\Aggregate\CryptoSpecification;
use App\Domain\Common\ValueObject\Cryptography\DigestAlgorithm;
use App\Domain\Common\ValueObject\Cryptography\EncryptionAlgorithm;
use App\Domain\Common\ValueObject\Cryptography\EncryptionPassphrase;
use GuzzleHttp\Psr7\BufferStream;
use Jsq\EncryptionStreams\AesDecryptingStream;
use Jsq\EncryptionStreams\AesEncryptingStream;
use Jsq\EncryptionStreams\Cbc;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

abstract class AESCryptoService implements CryptoService
{
    private const SEPARATOR = '@RiotKit_FR@';

    private string $secret;
    private string $salt;
    private CryptoSpecification $systemWideSpec;
    private LoggerInterface $logger;

    public function __construct(string $secret, string $salt, string $encAlgorithm, string $digestAlgorithm,
                                int $digestRounds, LoggerInterface $logger)
    {
        $this->secret          = $secret;
        $this->salt            = $salt;
        $this->logger          = $logger;
        $this->systemWideSpec = new CryptoSpecification(
            new EncryptionPassphrase($secret),
            new EncryptionAlgorithm($encAlgorithm),
            new DigestAlgorithm($digestAlgorithm, $digestRounds, $salt)
        );
    }

    public function decode(StreamInterface $inEncStream, string $iv, CryptoSpecification $spec = null): StreamInterface
    {
        $spec         = $spec ?: $this->systemWideSpec;
        $cipherMethod = new Cbc(hex2bin($iv), $spec->getCryptoAlgorithm()->getKeyBits());

        $key = openssl_pbkdf2(
            $spec->getPassphrase()->getValue(),
            $iv,
            $spec->getCryptoAlgorithm()->getKeySize(),
            $spec->getDigestAlgorithm()->getRounds(),
            $spec->getDigestAlgorithm()->getName()
        );

        return new AesDecryptingStream($inEncStream, $key, $cipherMethod);
    }

    public function encode(StreamInterface $inStream, CryptoSpecification $spec = null): CryptoStream
    {
        $spec = $spec ?: $this->systemWideSpec;

        if (!$spec->getPassphrase()->getValue()) {
            throw new \InvalidArgumentException('Passphrase cannot be empty');
        }

        $iv           = $spec->getCryptoAlgorithm()->generateInitializationVector();
        $cipherMethod = new Cbc($iv, $spec->getCryptoAlgorithm()->getKeyBits());
        $key = openssl_pbkdf2(
            $spec->getPassphrase()->getValue(),
            bin2hex($iv),
            $spec->getCryptoAlgorithm()->getKeySize(),
            $spec->getDigestAlgorithm()->getRounds(),
            $spec->getDigestAlgorithm()->getName()
        );

        $this->logger->debug(
            'openssl enc -d -' . $spec->getCryptoAlgorithm()->getValue() . ' ' .
            '-pbkdf2 -iter ' . $spec->getDigestAlgorithm()->getRounds() . ' -salt ' .
            '-md ' . $spec->getDigestAlgorithm()->getName() . ' -K ' . bin2hex($key) . ' -iv ' . bin2hex($iv)
        );

        return new CryptoStream(bin2hex($iv), new AesEncryptingStream($inStream, $key, $cipherMethod));
    }

    public function encodeString(string $input, CryptoSpecification $spec = null): string
    {
        $spec = $spec ?: $this->systemWideSpec;

        $inputAsStream = new BufferStream();
        $inputAsStream->write($input);

        $cryptoStream = $this->encode($inputAsStream, $spec);
        $cipherText   = $cryptoStream->getStream()->getContents();

        $this->logger->debug('AESCryptoService::encodeString.len(cipherText) == ' . mb_strlen($cipherText));

        return base64_encode(
            $cryptoStream->getIv() . self::SEPARATOR . $cipherText
        );
    }

    public function decodeString(string $input, CryptoSpecification $spec = null): string
    {
        $spec = $spec ?: $this->systemWideSpec;

        // extract IV and CipherText
        $decodedFromBase64 = base64_decode($input);
        $separatorPos      = strpos($decodedFromBase64, self::SEPARATOR);
        $iv                = substr($decodedFromBase64, 0, $separatorPos);
        $cipherText        = substr($decodedFromBase64, strlen($iv) + strlen(self::SEPARATOR));

        // convert to buffer, as interface requires that
        $cipherBuffer = new BufferStream();
        $cipherBuffer->write($cipherText);

        // decode into output buffer
        return $this->decode($cipherBuffer, $iv, $spec)->getContents();
    }

    public function hashString(string $input, CryptoSpecification $spec = null): string
    {
        $spec = $spec ?: $this->systemWideSpec;

        return hash($spec->getDigestAlgorithm()->getName(), $spec->getDigestAlgorithm()->getSalt() . '_' . $input);
    }
}
