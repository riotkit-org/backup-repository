<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\UploadFileByPostHandler;
use App\Domain\Storage\Form\UploadByPostForm;
use App\Infrastructure\Authentication\Token\TokenTransport;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Storage\Form\UploadByPostFormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class UploadByPostController extends BaseController
{
    private UploadFileByPostHandler $handler;

    public function __construct(UploadFileByPostHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Upload a file by POST
     *
     * @SWG\Post(
     *     description="Sends a raw file content or HTTP multipart form to the server to submit a file",
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
     *         name="fileName",
     *         type="string",
     *         in="query"
     *     ),
     *
     *     @SWG\Parameter(
     *         in="body",
     *         name="body",
     *         type="string",
     *         description="Raw file content or HTTP multipart form",
     *         @SWG\Schema()
     *     )
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Information about uploaded file",
     *     @SWG\Schema(
     *         type="object",
     *         ref=@Model(type=\App\Domain\Storage\Response\Docs\FileUploadedResponse::class)
     *     )
     * )
     *
     * @param Request $request
     * @param TokenTransport $tokenTransport
     * @param string $filename
     *
     * @return Response
     */
    public function handle(Request $request, TokenTransport $tokenTransport, string $filename = ''): Response
    {
        return $this->withLongExecutionTimeAllowed(
            function () use ($request, $tokenTransport, $filename) {
                return $this->handleInternally($request, $tokenTransport, $filename);
            }
        );
    }

    private function handleInternally(Request $request, TokenTransport $tokenTransport, string $filename = ''): Response
    {
        $filename = $this->applyFilenameFromMultipartIfParamEmpty($filename, $request);

        // REST support
        if ($filename) {
            $request->query->set('fileName', $filename);
        }

        $form = new UploadByPostForm();
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, UploadByPostFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $tokenTransport) {
                $appResponse = $this->handler->handle(
                    $form,
                    $tokenTransport->getToken()
                );

                return new JsonFormattedResponse(
                    $appResponse,
                    $appResponse->getExitCode()
                );
            }
        );
    }

    private function applyFilenameFromMultipartIfParamEmpty(string $filename, Request $request): string
    {
        if ($filename) {
            return $filename;
        }

        if ($request->files->count() > 0) {
            /**
             * @var UploadedFile[] $indexedNumerically
             */
            $indexedNumerically = \array_values($request->files->all());

            return (string) $indexedNumerically[0]->getClientOriginalName();
        }

        return '';
    }
}
