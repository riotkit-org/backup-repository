<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\DeleteFileHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\DeleteFileForm;
use App\Infrastructure\Storage\Form\DeleteFileFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteFileController extends BaseController
{
    /**
     * @var DeleteFileHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(DeleteFileHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    public function handle(Request $request, string $filename): Response
    {
        $form = new DeleteFileForm();
        $form->filename = $filename;
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, DeleteFileFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createDeleteContextFromTokenAndForm($this->getLoggedUserToken(), $form)
                );

                return new JsonResponse(
                    ['success' => $response],
                    $response ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        );
    }
}
