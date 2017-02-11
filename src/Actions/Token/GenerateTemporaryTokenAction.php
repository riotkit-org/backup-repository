<?php declare(strict_types=1);

namespace Actions\Token;

use Actions\AbstractBaseAction;
use Manager\Domain\TokenManagerInterface;

/**
 * @package Actions\ServerInfo
 */
class GenerateTemporaryTokenAction extends AbstractBaseAction
{
    /** @var TokenManagerInterface $tokenManager */
    private $tokenManager;

    /** @var array $roles */
    private $roles = [];

    /** @var array $tokenData */
    private $tokenData = [];

    /**
     * @var string $expirationModifier
     */
    private $expirationModifier;

    /**
     * @param TokenManagerInterface $tokenManager
     * @return $this
     */
    public function setTokenManager(TokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return GenerateTemporaryTokenAction
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @param string $expirationModifier
     * @return GenerateTemporaryTokenAction
     */
    public function setExpirationModifier(string $expirationModifier)
    {
        $this->expirationModifier = $expirationModifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpirationModifier()
    {
        return $this->expirationModifier;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $token = $this->tokenManager->generateNewToken(
            $this->getRoles(),
            (new \DateTime())->modify($this->getExpirationModifier()),
            $this->tokenData
        );

        return [
            'tokenId' => $token->getId(),
            'expires' => $token->getExpirationDate()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param array $tokenData
     * @return GenerateTemporaryTokenAction
     */
    public function setTokenData(array $tokenData)
    {
        $this->tokenData = $tokenData;
        return $this;
    }
}
