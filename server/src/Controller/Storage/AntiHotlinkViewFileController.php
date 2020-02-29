<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Domain\Storage\ActionHandler\AntiHotlinkViewFileHandler;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

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

    /**
     * Serve files with Anti-Hotlink protection
     *
     * @SWG\Parameter(
     *     name="fileId",
     *     in="path",
     *     type="string",
     *     description="Filename"
     * )
     *
     * @SWG\Parameter(
     *     name="accessToken",
     *     in="path",
     *     type="string",
     *     description="Access token dynamically generated for current viewer"
     * )
     *
     * @SWG\Parameter(
     *     name="expirationTime",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="Access token expiration time, used for verification"
     * )
     *
     * @SWG\Parameter(
     *     name="Range",
     *     type="string",
     *     in="header",
     *     description="HTTP Byte-Range support"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns file contents"
     * )
     *
     * @param Request $request
     * @param string $accessToken
     * @param string $fileId
     * @param int|null $expirationTime
     *
     * @return Response
     *
     * @throws \Exception
     */
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
