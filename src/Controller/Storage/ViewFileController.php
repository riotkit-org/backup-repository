<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewFileController extends BaseController
{
    /**
     * @var ViewFileHandler
     */
    private $handler;

    public function __construct(ViewFileHandler $handler)
    {
        $this->handler = $handler;
    }

    public function handle(string $filename): Response
    {
        $response = $this->handler->handle($filename);

        if ($response->getCode() === 200) {
            return new StreamedResponse($response->getResponseCallback());
        }

        return new JsonResponse($response, $response->getCode());
    }
}
