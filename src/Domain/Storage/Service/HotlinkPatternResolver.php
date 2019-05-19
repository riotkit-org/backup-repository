<?php declare(strict_types=1);

namespace App\Domain\Storage\Service;

use Psr\Log\LoggerInterface;

class HotlinkPatternResolver
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string md5|sha256
     */
    private $algorithm;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $pattern, string $algorithm, LoggerInterface $logger)
    {
        $this->pattern   = trim($pattern, "\n '\"");
        $this->algorithm = $algorithm;
        $this->logger    = $logger;
    }

    public function resolve(string $accessToken, string $filename, array $headers, array $query, array $server, ?int $expirationTime): bool
    {
        $this->logger->info('[PatternResolver] Got accessToken=' . $accessToken . ' to resolve for fileId=' . $filename);
        $this->logger->info('[PatternResolver] The pattern is "' . $this->pattern . '", algorithm is "' . $this->algorithm . '"');

        $toCompare = $this->pattern;
        $toCompare = \str_replace(['$http_x_expiration_time', '$expiration_time'], $expirationTime, $toCompare);
        $toCompare = \str_replace('$filename', $filename, $toCompare);

        foreach ($this->sortByLongestKey($headers) as $headerName => $values) {
            foreach ($values as $value) {
                $toCompare = str_replace('$http_' . $this->escapeVariableName($headerName), $value, $toCompare);
            }
        }

        foreach ($this->sortByLongestKey($query) as $queryName => $values) {
            foreach ($values as $value) {
                $toCompare = str_replace('$query_' . $this->escapeVariableName($queryName), $value, $toCompare);
            }
        }

        foreach ($this->sortByLongestKey($server) as $varName => $value) {
            $toCompare = str_replace('$server_' . $this->escapeVariableName($varName), $value, $toCompare);
        }

        $hashed = \hash($this->algorithm, $toCompare);
        $this->logger->info('[PatternResolver] Will compare requested "' . $accessToken . '" with raw="' . $toCompare . '", hashed="' . $hashed . '"');

        return $accessToken === $hashed;
    }

    private function escapeVariableName(string $headerName): string
    {
        return \strtolower(\str_replace('-', '_', $headerName));
    }

    private function sortByLongestKey(array $input): array
    {
        $asKeys = [];
        $finally = [];

        foreach ($input as $key => $value) {
            $asKeys[$key] = \strlen($key);
        }

        arsort($asKeys);

        foreach ($asKeys as $key => $len) {
            $finally[$key] = $input[$key];
        }

        return $finally;
    }
}