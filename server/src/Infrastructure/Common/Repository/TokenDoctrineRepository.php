<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Repository;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Common\Repository\TokenRepository;
use App\Domain\Roles;

/**
 * @codeCoverageIgnore
 */
abstract class TokenDoctrineRepository extends BaseRepository implements TokenRepository
{
    public function findTokenById(string $id, string $className = null)
    {
        if ($className === null) {
            $className = $this->getTokenClass();
        }

        if (Roles::isTestToken($id)) {
            /**
             * @var Token $token
             */
            $token = new $className();
            $token->setId(Roles::TEST_TOKEN);
            $token->setRoles([Roles::ROLE_ADMINISTRATOR]);

            return $token;
        }

        return $this->_em->find($className, $id);
    }

    protected function getTokenClass(): string
    {
        return Token::class;
    }
}
