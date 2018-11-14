<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\UploadFileByPostHandler;
use App\Domain\Storage\Form\UploadByPostForm;
use App\Infrastructure\Storage\Form\UploadFormType;
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

    public function handle(Request $request): JsonResponse
    {
        $form = new UploadByPostForm();
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, UploadFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        $appResponse = $this->handler->handle(
            $form,
            $this->createBaseUrl($request),
            $token
        );

        return new JsonResponse(
            $appResponse,
            $appResponse->getExitCode()
        );
    }
}
