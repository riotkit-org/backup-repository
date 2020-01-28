<?php declare(strict_types=1);

namespace App\Infrastructure\Technical\Service;

use App\Domain\Technical\Service\StatusChecker;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class GuzzleStatusChecker implements StatusChecker
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getHealthStatus(string $url, string $code): string
    {
        $url = rtrim($url, ' /');

        $guzzle = new Client();

        try {
            $response = $guzzle->get($url . '/health?code=' . $code);

        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $this->logger->debug('HTTP returned: ' . $exception->getMessage());

            if ($exception->getCode() === 403) {
                return self::STATUS_NOT_AUTHORIZED;
            }

            if ($exception->getCode() >= 500) {
                $json = \json_decode($response->getBody()->getContents(), true, 24);

                if (\is_array($json)) {
                    if (!$json['status']['storage']) {
                        return self::STATUS_STORAGE_NOT_READY;
                    }

                    if (!$json['status']['database']) {
                        return self::STATUS_DB_NOT_READY;
                    }
                }

                return self::STATUS_NOT_HEALTHY;
            }
        }

        return self::STATUS_RUNNING;
    }
}
