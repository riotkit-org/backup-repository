<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\UploadFileByPostHandler;
use App\Domain\Storage\Form\UploadByPostForm;
use App\Infrastructure\Authentication\Token\TokenTransport;
use App\Infrastructure\Storage\Form\UploadByPostFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UploadByPostController extends BaseController
{
    /**
     * @var UploadFileByPostHandler
     */
    private $handler;

    public function __construct(UploadFileByPostHandler $handler)
    {
        $this->handler = $handler;
    }

    public function handle(Request $request, TokenTransport $tokenTransport, string $filename = ''): JsonResponse
    {
        // REST support
        if ($filename) {
            $request->query->set('fileName', $filename);
        }

        $form = new UploadByPostForm();
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, UploadByPostFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        $appResponse = $this->handler->handle(
            $form,
            $this->createBaseUrl($request),
            $tokenTransport->getToken()
        );

        return new JsonResponse(
            $appResponse,
            $appResponse->getExitCode()
        );
    }
}
