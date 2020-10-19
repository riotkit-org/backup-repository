<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Security\DisallowTokenHandler;
use App\Domain\Backup\ActionHandler\Security\GrantUserToCollection;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\CollectionAddDeleteUserForm;
use App\Domain\Backup\Form\UserAccessAttachForm;
use App\Domain\Backup\Form\UserAccessRevokeForm;
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
    private GrantUserToCollection $attachingHandler;
    private DisallowTokenHandler $detachingHandler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        GrantUserToCollection $attachingHandler,
        DisallowTokenHandler $detachingHandler,
        SecurityContextFactory $authFactory
    ) {
        $this->attachingHandler = $attachingHandler;
        $this->detachingHandler = $detachingHandler;
        $this->authFactory      = $authFactory;
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
        $decoded = json_decode($request->getContent(), true);

        $formData = [
            'collection' => $id,
            'user'       => $decoded['token'] ?? '', // @todo change to user
            'roles'      => $decoded['roles'] ?? []
        ];

        /**
         * @var UserAccessAttachForm|UserAccessRevokeForm $form
         */
        if ($this->isDeletionRequest($request)) {
            $form = $this->decodeRequestIntoDTO($formData,UserAccessRevokeForm::class);
        } else {
            $form = $this->decodeRequestIntoDTO($formData,UserAccessAttachForm::class);
        }

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);
        $response = $this->getHandler($request)->handle($form, $this->authFactory->createCollectionManagementContext($user, $form->collection));

        if ($request->query->get('simulate') !== 'true') {
            $this->getHandler($request)->flush();
        }

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }

    /**
     * @param Request $request
     *
     * @return DisallowTokenHandler|GrantUserToCollection
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
}
