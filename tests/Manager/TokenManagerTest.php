<?php declare(strict_types=1);

namespace Tests\Manager;

use Manager\Domain\TokenManagerInterface;
use Model\Entity\Token;
use Repository\Domain\TokenRepositoryInterface;
use Tests\WolnosciowiecTestCase;

/**
 * @see TokenManagerInterface
 * @package Tests\Manager
 */
class TokenManagerTest extends WolnosciowiecTestCase
{
    /**
     * @return TokenManagerInterface
     */
    private function getManager()
    {
        return $this->app->offsetGet('manager.token');
    }

    /**
     * @see TokenManagerInterface::generateNewToken()
     * @return Token
     */
    public function testGenerateNewToken()
    {
        $this->prepareDatabase();

        $token = $this->getManager()->generateNewToken(['syndicate_distribution'], new \DateTime('2030-05-05'));

        $this->assertNotEmpty($token->getId());
        $this->assertSame('2030-05-05', $token->getExpirationDate()->format('Y-m-d'));
        $this->assertSame(date('Y-m-d'), $token->getCreationDate()->format('Y-m-d'));

        return $token;
    }

    /**
     * @depends testGenerateNewToken
     * @param Token $token
     *
     * @return Token
     */
    public function testIsTokenValid(Token $token)
    {
        $this->assertTrue(
            $this->getManager()->isTokenValid($token->getId(), 'syndicate_distribution'),
            'Failed to match that the token is valid for "syndicate_distribution" role'
        );

        $this->assertFalse(
            $this->getManager()->isTokenValid($token->getId(), 'production'),
            'Required role is "production", but the token was not registered with it, ' .
            'so the validation should return false'
        );

        $this->assertFalse(
            $this->getManager()->isTokenValid('unknown-token', 'syndicate_distribution'),
            '"unknown-token" does not exists, so the validation should return false'
        );

        $this->assertTrue(
            $this->getManager()->isTokenValid($this->app->offsetGet('api.key'), 'syndicate_distribution'),
            'Main API key (configurable via configuration file) should have access to everything'
        );

        return $token->getId();
    }

    /**
     * @depends testIsTokenValid
     * @param string $tokenId
     */
    public function testRemoveToken(string $tokenId)
    {
        /** @var TokenRepositoryInterface $repository */
        $repository = $this->app->offsetGet('repository.token');
        $token = $repository->getTokenById($tokenId);
        $tokenCopy = clone $token;

        $this->getManager()->removeToken($token);

        $this->assertFalse(
            $this->getManager()->isTokenValid($tokenCopy->getId(), 'syndicate_distribution'),
            'Failed to match that the token is valid for "syndicate_distribution" role'
        );
    }
}
