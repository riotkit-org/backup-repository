<?php declare(strict_types=1);

namespace Tests\Repository;

use Manager\Domain\TokenManagerInterface;
use Repository\Domain\TokenRepositoryInterface;
use Tests\WolnosciowiecTestCase;

/**
 * @see TokenRepositoryInterface
 * @package Tests\Repository
 */
class TokenRepositoryTest extends WolnosciowiecTestCase
{
    /**
     * @see TokenRepositoryInterface::getTokenById()
     * @see TokenRepositoryInterface::getExpiredTokens()
     */
    public function testTokenFlow()
    {
        $this->prepareDatabase();

        /**
         * @var TokenManagerInterface    $tokenManager
         * @var TokenRepositoryInterface $repository
         */
        $tokenManager = $this->app->offsetGet('manager.token');
        $repository   = $this->app->offsetGet('repository.token');

        // generate an expired token
        $token = $tokenManager->generateNewToken(['militant'], (new \DateTime())->modify('-30 m'));

        // getTokenById()
        $this->assertSame($token->getId(), $repository->getTokenById($token->getId())->getId());

        // getExpiredTokens()
        $this->assertSame($token->getId(), $repository->getExpiredTokens()[0]->getId());
    }
}