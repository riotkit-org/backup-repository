<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Repository;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Common\Repository\TokenRepository;
use App\Domain\Common\ValueObject\Roles as RolesVO;
use App\Domain\Roles as RolesDomain;

/**
 * @codeCoverageIgnore
 */
abstract class TokenDoctrineRepository extends BaseRepository implements TokenRepository
{
    public function findUserByUserId(string $id, string $className = null)
    {
        if ($className === null) {
            $className = $this->getTokenClass();
        }

        if (RolesDomain::isTestToken($id) || RolesDomain::isInternalApplicationToken($id)) {
            /**
             * @var Token $token
             */
            $token = new $className();
            $token->setId($id);
            $token->setRoles(RolesVO::fromArray([RolesDomain::ROLE_ADMINISTRATOR]));

            return $token;
        }

        return $this->_em->find($className, $id);
    }

    public function findApplicationInternalToken(): Token
    {
        return $this->findUserByUserId(RolesDomain::INTERNAL_CONSOLE_TOKEN, Token::class);
    }

    protected function getTokenClass(): string
    {
        return Token::class;
    }
}
