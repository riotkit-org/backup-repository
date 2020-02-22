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

class UploadByPostController extends BaseController
{
    private UploadFileByPostHandler $handler;

    public function __construct(UploadFileByPostHandler $handler)
    {
        $this->handler = $handler;
    }

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
