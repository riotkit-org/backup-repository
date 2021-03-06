<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Configuration\PasswordHashingConfiguration;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Form\AuthForm;
use App\Domain\Authentication\Manager\UserManager;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Response\UserCRUDResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Authentication\ValueObject\Password;
use App\Domain\Common\Exception\ResourceNotFoundException;
use App\Domain\Common\SharedEntity\EntityValidationTrait;
use Exception;

/**
 * User Account editing
 * ====================
 *   Allows to edit permissions, text fields and change password.
 */
class UserEditHandler
{
    use EntityValidationTrait;

    private UserManager $tokenManager;
    private UserRepository $repository;
    private PasswordHashingConfiguration $hashingConfiguration;

    public function __construct(UserManager $manager, UserRepository $repository, PasswordHashingConfiguration $hashingConfiguration)
    {
        $this->tokenManager = $manager;
        $this->repository = $repository;
        $this->hashingConfiguration = $hashingConfiguration;
    }

    /**
     * @param AuthForm $form
     * @param AuthenticationManagementContext $context
     *
     * @return UserCRUDResponse
     *
     * @throws Exception
     */
    public function handle(AuthForm $form, AuthenticationManagementContext $context): UserCRUDResponse
    {
        $user = $this->repository->findOneById($form->id);

        if (!$user) {
            throw ResourceNotFoundException::createFromMessage('User not found');
        }

        $constructPassword = function (string $password) use ($user) {
            if (!$password) {
                return Password::fromEmpty();
            }

            return Password::fromString($password, $user->getSalt(), $this->hashingConfiguration);
        };

        $isChangingPassword = $form->password && $form->repeatPassword;
        $currentPassword    = null;
        $newPassword        = null;
        $repeatPassword     = null;

        static::withValidationErrorAggregation([
            static function () use (&$currentPassword, $constructPassword, $form) {
                $currentPassword = $constructPassword($form->currentPassword);
            },
            static function () use (&$newPassword, $constructPassword, $form) {
                $newPassword = $constructPassword($form->password);
            },
            static function () use (&$repeatPassword, $constructPassword, $form) {
                $repeatPassword = $constructPassword($form->repeatPassword);
            }
        ]);

        $this->assertHasRights(
            $context, $user, $form->permissions, $currentPassword,
            $newPassword, $repeatPassword, $isChangingPassword
        );

        $this->tokenManager->editUser(
            $user,
            $form->permissions,
            $form->expires,
            $form->organization,
            $form->about,
            $form->data->toPersistableForm()
        );

        if ($isChangingPassword) {
            $this->tokenManager->changePassword($user, $newPassword);
        }

        $this->tokenManager->flushAll();

        return UserCRUDResponse::createUserEditedResponse($user);
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param User $user
     * @param array $permissions
     * @param Password $currentPassword
     * @param Password $newPassword
     * @param Password $repeatNewPassword
     * @param bool $isChangingPassword
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context, User $user, array $permissions,
                                     Password $currentPassword, Password $newPassword, Password $repeatNewPassword,
                                     bool $isChangingPassword): void
    {
        if ($isChangingPassword && !$context->canChangePassword($user, $currentPassword, $newPassword, $repeatNewPassword)) {
            throw AuthenticationException::fromCannotChangePassword();
        }

        if (!$context->canEditUser($user, $permissions)) {
            throw AuthenticationException::fromUsersEditProhibition();
        }
    }
}
