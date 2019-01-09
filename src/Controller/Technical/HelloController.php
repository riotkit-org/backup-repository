<?php declare(strict_types=1);

namespace App\Controller\Technical;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Lists all public routes
 */
class HelloController extends AbstractController
{
    public function sayHelloAction(): JsonResponse
    {
        return new JsonResponse(
            'Hello, welcome. Please take a look at /repository/routing/map for the list of available routes.',
            JsonResponse::HTTP_OK
        );
    }
}
