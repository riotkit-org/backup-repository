<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Token;

use App\Domain\Authentication\Entity\Token;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\User;

class TokenTransport extends AbstractToken
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var Token
     */
    private $token;

    public function __construct(string $secret, Token $token)
    {
        parent::__construct($token->getRoles());

        $this->secret = $secret;
        $this->token = $token;
        $this->setAuthenticated(true);
        $this->setUser(new User('anonymous', $secret, $token->getRoles()));
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
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }
}
