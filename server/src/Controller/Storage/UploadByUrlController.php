<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Common\Exception\RequestException;
use App\Domain\Storage\ActionHandler\UploadFileByUrlHandler;
use App\Domain\Storage\Form\UploadByUrlForm;
use App\Infrastructure\Authentication\Token\TokenTransport;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Storage\Form\UploadByUrlFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class UploadByUrlController extends BaseController
{
    private UploadFileByUrlHandler $handler;

    public function __construct(UploadFileByUrlHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Upload a file from external URL address
     *
     * @SWG\Post(
     *     description="Tell the API to download external URL address as a file upload",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="type",
     *         type="string",
     *         in="path",
     *         default="file"
     *     ),
     *
     *     @SWG\Parameter(
     *         in="body",
     *         name="body",
     *         description="JSON payload",
     *
     *         @SWG\Schema(
     *             type="object",
     *             required={"fileUrl"},
     *             @SWG\Property(property="fileUrl", example="https://iwa-ait.org/sites/default/files/iwaait_1.png", type="string"),
     *         )
     *     )
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Information about file",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=\App\Domain\Storage\Response\Docs\FileUploadedResponse::class)
     *     )
     * )
     *
     * @param Request $request
     * @param TokenTransport $tokenTransport
     *
     * @return Response
     */
    public function handle(Request $request, TokenTransport $tokenTransport): Response
    {
        return $this->withLongExecutionTimeAllowed(
            function () use ($request, $tokenTransport) {
                return $this->handleInternally($request, $tokenTransport);
            }
        );
    }

    private function handleInternally(Request $request, TokenTransport $tokenTransport): Response
    {
        $form = new UploadByUrlForm();

        try {
            $infrastructureForm = $this->submitFormFromJsonRequest($request, $form, UploadByUrlFormType::class);

        } catch (RequestException $requestException) {
            return $this->createRequestExceptionResponse($requestException);
        }

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $tokenTransport, $request) {
                $appResponse = $this->handler->handle($form, $tokenTransport->getToken());

                return new JsonFormattedResponse(
                    $appResponse,
                    $appResponse->getExitCode()
                );
            }
        );
    }
}
