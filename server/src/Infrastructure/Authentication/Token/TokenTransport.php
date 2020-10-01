<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Token;

use App\Domain\Authentication\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\User as SymfonyUser;

/**
 * @todo: JWT - verify if this class is still needed
 */
class TokenTransport extends AbstractToken
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var ?User
     */
    private $user;

    public function __construct(string $secret, User $user)
    {
        parent::__construct($user->getRoles());

        $this->secret = $secret;
        $this->user   = $user;

        $this->setAuthenticated(true);
        $this->setUser(new SymfonyUser('anonymous', $secret, $user->getRoles()));
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * Returns the secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->secret, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->secret, $parentStr) = \unserialize($serialized);
        parent::unserialize($parentStr);
    }

    /**
     * @return ?User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}
