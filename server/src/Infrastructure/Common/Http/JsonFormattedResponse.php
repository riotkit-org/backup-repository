<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonFormattedResponse extends JsonResponse
{
    public function __construct($data = null, int $status = 200, array $headers = [])
    {
        parent::__construct(json_encode($data, JSON_PRETTY_PRINT), $status, $headers, true);
    }
}
