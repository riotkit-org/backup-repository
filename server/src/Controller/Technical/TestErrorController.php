<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Infrastructure\Common\Exception\HttpError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Throws errors for testing error response
 */
class TestErrorController extends AbstractController
{
    public function serveInternalServerError()
    {
        throw HttpError::fromInternalServerError();
    }
}
