<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Domain\Storage\ActionHandler\AntiHotlinkViewFileHandler;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anti-Hotlink protection for ViewFileController
 * Works just like NGINX's secure-link module.
 *
 * Requires ANTI_HOTLINK_PROTECTION_ENABLED to be set to true and other configuration variables to be properly set.
 */
class AntiHotlinkViewFileController extends ViewFileController
{
    private AntiHotlinkViewFileHandler $antiHotlinkViewFileHandler;

    public function __construct(
        ViewFileHandler $handler,
        SecurityContextFactory $authFactory,
        AntiHotlinkViewFileHandler $antiHotlinkViewFileHandler
    ) {
        $this->antiHotlinkViewFileHandler = $antiHotlinkViewFileHandler;

        parent::__construct($handler, $authFactory);
    }

    public function handleAntiHotlinkUrl(Request $request, string $accessToken, string $fileId, ?int $expirationTime = null): Response
    {
        $response = $this->antiHotlinkViewFileHandler->handle(
            $accessToken,
            $fileId,
            $request->headers->all(),
            $request->query->all(),
            $request->server->all(),
            $expirationTime
        );

        //
        // When the anti-hotlink token is OK, then pass the request to the second controller - ViewFileController
        //
        if ($response->isSuccess()) {
            return $this->handle($request, $response->getFilename()->getValue());
        }

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
