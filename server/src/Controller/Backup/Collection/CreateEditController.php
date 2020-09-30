<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\CreationHandler;
use App\Domain\Backup\ActionHandler\Collection\EditHandler;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Form\Collection\EditForm;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Infrastructure\Backup\Form\Collection\CreationFormType;
use App\Infrastructure\Backup\Form\Collection\EditFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class CreateEditController extends BaseController
{
    private CreationHandler $createHandler;
    private EditHandler $editHandler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        CreationHandler $handler,
        EditHandler $editHandler,
        SecurityContextFactory $authFactory
    ) {
        $this->createHandler = $handler;
        $this->editHandler   = $editHandler;
        $this->authFactory   = $authFactory;
    }

    /**
     * Create (POST), edit (PUT) a versioned file-collection that will keep historic versions of file, and rotate them.
     *
     * @SWG\Parameter(
     *     type="boolean",
     *     in="query",
     *     name="simulate",
     *     description="Set to true to only simulate request, without commiting the changes. Optional parameter."
     * )
     *
     * @SWG\Response(
     *     response="201",
     *     description="Collection was successfuly created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="status",
     *             type="boolean",
     *             example="true"
     *         ),
     *         @SWG\Property(
     *             property="http_code",
     *             type="integer",
     *             example="201"
     *         ),
     *         @SWG\Property(
     *             property="error_code",
     *             type="integer",
     *             example="50091"
     *         ),
     *         @SWG\Property(
     *             property="errors",
     *             type="array",
     *             @SWG\Items(type="string")
     *         ),
     *         @SWG\Property(
     *             property="message",
     *             type="string",
     *             example="OK"
     *         ),
     *         @SWG\Property(
     *             property="collection",
     *             ref=@Model(type=\App\Domain\Backup\Entity\Docs\Collection::class)
     *         ),
     *          @SWG\Property(
     *             property="context",
     *             type="array",
     *             @SWG\Items(type="string")
     *         )
     *     )
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Collection was modified",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="status",
     *             type="boolean",
     *             example="true"
     *         ),
     *         @SWG\Property(
     *             property="http_code",
     *             type="integer",
     *             example="201"
     *         ),
     *         @SWG\Property(
     *             property="error_code",
     *             type="integer",
     *             example="50091"
     *         ),
     *         @SWG\Property(
     *             property="errors",
     *             type="array",
     *             @SWG\Items(type="string")
     *         ),
     *         @SWG\Property(
     *             property="message",
     *             type="string",
     *             example="OK"
     *         ),
     *         @SWG\Property(
     *             property="collection",
     *             ref=@Model(type=\App\Domain\Backup\Entity\Docs\Collection::class)
     *         ),
     *          @SWG\Property(
     *             property="context",
     *             type="array",
     *             @SWG\Items(type="string")
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handleAction(Request $request): Response
    {
        $isCreation = strtoupper($request->getMethod()) === 'POST';

        $form = $isCreation ? new CreationForm() : new EditForm();
        $infrastructureForm = $this->submitFormFromJsonRequest(
            $request,
            $form,
            $isCreation ? CreationFormType::class : EditFormType::class
        );

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request, $isCreation) {
                $response = $this->handle(
                    $form,
                    $this->authFactory->createCollectionManagementContext($this->getLoggedUser()),
                    $isCreation
                );

                if ($request->query->get('simulate') !== 'true') {
                    $this->createHandler->flush();
                }

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }

    /**
     * @param $form
     * @param $auth
     * @param bool $isCreation
     *
     * @return CrudResponse
     *
     * @throws AuthenticationException
     */
    private function handle($form, $auth, bool $isCreation): CrudResponse
    {
        if ($isCreation) {
            return $this->createHandler->handle($form, $auth);
        }

        return $this->editHandler->handle($form, $auth);
    }
}
