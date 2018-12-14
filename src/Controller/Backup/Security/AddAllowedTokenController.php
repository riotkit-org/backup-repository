<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Security\TokenAddHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Storage\Form\TokenAttachForm;
use App\Infrastructure\Backup\Form\Collection\TokenAttachFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attach a token of given Id to the collection
 */
class AddAllowedTokenController extends BaseController
{
    /**
     * @var TokenAddHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(TokenAddHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $id): Response
    {
        $form = new TokenAttachForm();
        $infrastructureForm = $this->createForm(TokenAttachFormType::class, $form);
        $infrastructureForm->submit([
            'collection' => $id,
            'token'      => $this->getTokenIdFromRequest($request)
        ]);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createCollectionManagementContext($this->getLoggedUserToken())
                );

                if ($request->query->get('simulate') !== 'true') {
                    $this->handler->flush();
                }

                return new JsonResponse($response, $response->getHttpCode());
            }
        );
    }

    private function getTokenIdFromRequest(Request $request): string
    {
        $json = json_decode($request->getContent(false), true);

        return isset($json['token']) ? (string)$json['token'] : '';
    }
}
