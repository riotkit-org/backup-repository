<?php declare(strict_types=1);

namespace App\Domain\Replication\Service;

use App\Domain\Replication\DTO\Encryption;

class FileEncryptionService
{
    /**
     * @param resource $resource
     * @param resource $output
     */
    public function encryptStream($input, $output, string $password,
                                  string $method = 'aes-128-cbc', int $bufferSize = 1024 * 1024)
    {
        $iv = \random_bytes(\openssl_cipher_iv_length($method));

        return new Encryption($iv, $password, $method, function () use ($input, $output, $method, $password, $bufferSize, $iv) {
            while (!feof($input)) {
                $chunk = fread($input, $bufferSize);
                fwrite($output, openssl_encrypt($chunk, $method, $password, OPENSSL_RAW_DATA, $iv));
            }
        });
    }

    public function generateShellCommand(string $algorithm, string $password, string $iv)
    {
        $template = 'openssl enc -d -%algorithm_name% -K "%passphrase%" -iv "%iv%"';

        return str_replace(
            ['%algorithm_name%', '%passphrase%', '%iv%'],
            [$algorithm, bin2hex($password), $iv],
            $template
        );
    }
}
