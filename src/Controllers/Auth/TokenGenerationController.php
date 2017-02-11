<?php declare(strict_types=1);

namespace Controllers\Auth;

use Actions\Token\GenerateTemporaryTokenAction;
use Controllers\AbstractBaseController;
use Model\Permissions\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Generation of temporary tokens
 * that allows persons from public
 * to execute a single action
 * ==============================
 *
 * @package Controllers\Auth
 */
class TokenGenerationController extends AbstractBaseController
{
    /**
     * @return JsonResponse
     */
    public function generateTemporaryTokenAction()
    {
        $token = json_decode($this->getRequest()->getContent(), true);
        $roles = $token['roles'] ?? [];
        $data  = $token['data'] ?? [];

        if (!is_array($roles) || empty($roles)) {
            return new JsonResponse([
                'success'         => false,
                'error'           => 'No roles specified, please specify roles in the POST body as JSON eg. {"roles": ["role1", "role2"], "data": {}}',
                'roles_available' => [Roles::ROLE_UPLOAD_IMAGES],
            ], 400);
        }

        $action = new GenerateTemporaryTokenAction();
        $action->setTokenManager($this->getContainer()->offsetGet('manager.token'));
        $action->setRoles($roles);
        $action->setExpirationModifier($this->getContainer()->offsetGet('token.expiration.time') ?? '+30 minutes');
        $action->setTokenData($data);

        return new JsonResponse([
            'success' => true,
            'data'    => $action->execute(),
        ]);
    }
}
