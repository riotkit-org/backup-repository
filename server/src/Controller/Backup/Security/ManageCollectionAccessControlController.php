<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Security\DisallowTokenHandler;
use App\Domain\Backup\ActionHandler\Security\TokenAddHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\CollectionAddDeleteTokenForm;
use App\Domain\Backup\Form\TokenFormAttachForm;
use App\Domain\Backup\Form\TokenDeleteForm;
use App\Infrastructure\Backup\Form\Collection\TokenAttachFormType;
use App\Infrastructure\Backup\Form\Collection\TokenDeleteFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attach/detach a token of given Id to the collection
 */
abstract class ManageCollectionAccessControlController extends BaseController
{
    private TokenAddHandler $attachingHandler;
    private DisallowTokenHandler $detachingHandler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        TokenAddHandler $attachingHandler,
        DisallowTokenHandler $detachingHandler,
        SecurityContextFactory $authFactory
    ) {
        $this->attachingHandler = $attachingHandler;
        $this->detachingHandler = $detachingHandler;
        $this->authFactory = $authFactory;
    }

    /**
     * Manage allowed tokens in a collection
     *
     * @param Request $request
     * @param string  $id
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handleAction(Request $request, string $id): Response
    {
        $formData = [
            'collection' => $id,
            'token'      => $this->getTokenIdFromRequest($request)
        ];

        /**
         * @var TokenFormAttachForm|TokenDeleteForm $form
         */
        if ($this->isDeletionRequest($request)) {
            $form = $this->decodeRequestIntoDTO($formData,TokenDeleteForm::class);
        } else {
            $form = $this->decodeRequestIntoDTO($formData,TokenFormAttachForm::class);
        }

        $response = $this->getHandler($request)->handle(
            $form,
            $this->authFactory->createCollectionManagementContext($this->getLoggedUser())
        );

        if ($request->query->get('simulate') !== 'true') {
            $this->getHandler($request)->flush();
        }

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }

    /**
     * @param Request $request
     *
     * @return DisallowTokenHandler|TokenAddHandler
     */
    private function getHandler(Request $request)
    {
        if ($this->isDeletionRequest($request)) {
            return $this->detachingHandler;
        }

        return $this->attachingHandler;
    }

    private function isDeletionRequest(Request $request): bool
    {
        return $request->getMethod() === 'DELETE';
    }

    private function getTokenIdFromRequest(Request $request): string
    {
        if ($request->attributes->get('tokenId')) {
            return $request->attributes->get('tokenId');
        }

        $json = json_decode($request->getContent(false), true);

        return isset($json['token']) ? (string)$json['token'] : '';
    }
}
