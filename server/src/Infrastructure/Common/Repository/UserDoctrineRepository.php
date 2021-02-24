<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Repository;

use App\Domain\Authentication\Entity\User;
use App\Domain\Common\Repository\UserRepository;
use App\Domain\Common\ValueObject\Permissions as RolesVO;
use App\Domain\PermissionsReference as RolesDomain;

/**
 * @codeCoverageIgnore
 */
abstract class UserDoctrineRepository extends BaseRepository implements UserRepository
{
    public function findUserByUserId(string $id, string $className = null)
    {
        if ($className === null) {
            $className = $this->getTokenClass();
        }

        if (RolesDomain::isTestToken($id) || RolesDomain::isInternalApplicationToken($id)) {
            /**
             * @var User $token
             */
            $token = new $className();
            $token->setId($id);
            $token->setPermissions(RolesVO::fromArray([RolesDomain::PERMISSION_ADMINISTRATOR]));

            return $token;
        }

        return $this->_em->find($className, $id);
    }

    public function findApplicationInternalToken(): User
    {
        return $this->findUserByUserId(RolesDomain::INTERNAL_CONSOLE_TOKEN, User::class);
    }

    protected function getTokenClass(): string
    {
        return User::class;
    }
}
