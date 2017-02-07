<?php declare(strict_types=1);

namespace Tests\Factory;

use Factory\TokenFactory;
use Tests\WolnosciowiecTestCase;

/**
 * @see TokenFactory
 * @package Tests\Service
 */
class TokenFactoryTest extends WolnosciowiecTestCase
{
    /**
     * @see TokenFactory::createNewToken()
     */
    public function testCreateNewToken()
    {
        /** @var TokenFactory $tokenFactory */
        $tokenFactory = $this->app->offsetGet('factory.token');
        $token        = $tokenFactory->createNewToken(['test_role', 'test_role_2'], new \DateTime('2040-05-05'));

        $this->assertEquals(['test_role', 'test_role_2'], $token->getRoles());
        $this->assertEquals((new \DateTime())->format('Y-m-d H:i'), $token->getCreationDate()->format('Y-m-d H:i'));
        $this->assertEquals('2040-05-05', $token->getExpirationDate()->format('Y-m-d'));
        $this->assertRegExp('/^[0-9A-Z]{14}\.[0-9]{8}$/i', $token->getId());
    }
}