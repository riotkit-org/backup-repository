<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\DeleteFileHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\DeleteFileForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Storage\Form\DeleteFileFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class DeleteFileController extends BaseController
{
    private DeleteFileHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(DeleteFileHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Delete a file from storage
     *
     * @SWG\Parameter(
     *     name="filename",
     *     type="string",
     *     in="path",
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="password",
     *     type="string",
     *     required=false,
     *     in="query",
     *     description="Required, when the file is password protected"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Deletes a file from the storage",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="result",
     *             type="boolean"
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param string $filename
     *
     * @return Response
     *
     * @throws \Exception
     */
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

                return new JsonFormattedResponse(
                    ['success' => $response],
                    $response ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        );
    }
}
