<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\UploadFileByUrlHandler;
use App\Domain\Storage\Form\UploadByUrlForm;
use App\Infrastructure\Authentication\Token\TokenTransport;
use App\Infrastructure\Storage\Form\UploadByUrlFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UploadByUrlController extends BaseController
{
    /**
     * @var UploadFileByUrlHandler
     */
    private $handler;

    public function __construct(UploadFileByUrlHandler $handler)
    {
        $this->handler = $handler;
    }

    public function uploadByUrlAction(Request $request, TokenTransport $tokenTransport): JsonResponse
    {
        $form = new UploadByUrlForm();
        $infrastructureForm = $this->submitFormFromJsonRequest($request, $form, UploadByUrlFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        $appResponse = $this->handler->handle($form, $this->createBaseUrl($request), $tokenTransport->getToken());

        return new JsonResponse(
            $appResponse,
            $appResponse->getExitCode()
        );
    }
}
