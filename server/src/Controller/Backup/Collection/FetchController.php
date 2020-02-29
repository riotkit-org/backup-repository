<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\FetchHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Infrastructure\Backup\Form\Collection\DeleteFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class FetchController extends BaseController
{
    private FetchHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        FetchHandler $handler,
        SecurityContextFactory $authFactory
    ) {
        $this->handler       = $handler;
        $this->authFactory   = $authFactory;
    }

    /**
     * @SWG\Parameter(
     *     type="string",
     *     in="path",
     *     name="id",
     *     description="Collection id"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Collection was found",
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
     *             example="200"
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
     * @param string  $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(string $id): Response
    {
        $form = new DeleteForm();
        $infrastructureForm = $this->createForm(DeleteFormType::class, $form);
        $infrastructureForm->submit([
            'collection' => $id
        ]);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                $securityContext = $this->authFactory->createCollectionManagementContext(
                    $this->getLoggedUserToken()
                );

                $response = $this->handler->handle($form, $securityContext);

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }
}
