<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenGenerationHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AuthForm;
use App\Infrastructure\Authentication\Form\AuthFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class GenerateTokenController extends BaseController
{
    private TokenGenerationHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(TokenGenerationHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Create a new token, assign roles, set expiration, upload policy
     *
     * @SWG\Post(
     *     description="Request to create a new access token",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         in="body",
     *         name="body",
     *         description="JSON payload",
     *
     *         @SWG\Schema(
     *             type="object",
     *             required={"id", "roles", "data"},
     *             @SWG\Property(property="id", example="ca6a2635-d2cb-4682-ba81-3879dd0e8a77", type="string"),
     *             @SWG\Property(property="roles", example={"collections.create_new", "collections.manage_tokens_in_allowed_collections"}, type="array", @SWG\Items(type="string")),
     *             @SWG\Property(property="expires", type="string", example="2021-05-01 01:06:01"),
     *             @SWG\Property(property="data", ref=@Model(type=\App\Domain\Authentication\Entity\Docs\TokenData::class))
     *         )
     *     )
     * )
     *
     * @SWG\Response(
     *     response="201",
     *     description="Token was successfuly created",
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
     *             property="errors",
     *             type="array",
     *             @SWG\Items(
     *                 type="string"
     *             )
     *         ),
     *         @SWG\Property(
     *             property="message",
     *             type="string",
     *             example="Token created"
     *         ),
     *         @SWG\Property(
     *             property="token",
     *             ref=@Model(type=\App\Domain\Authentication\Entity\Docs\Token::class)
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
     * @return JsonFormattedResponse
     *
     * @throws Exception
     */
    public function generateAction(Request $request): Response
    {
        $form = new AuthForm();
        $infrastructureForm = $this->submitFormFromJsonRequest($request, $form, AuthFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                return new JsonFormattedResponse(
                    $this->handler->handle(
                        $form,
                        $this->authFactory->createFromToken($this->getLoggedUserToken())
                    ),
                    JsonFormattedResponse::HTTP_CREATED
                );
            }
        );
    }
}
