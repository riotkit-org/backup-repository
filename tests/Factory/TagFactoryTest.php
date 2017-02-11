<?php declare(strict_types=1);

namespace Tests\Factory;

use Factory\Domain\TagFactoryInterface;
use Tests\WolnosciowiecTestCase;

/**
 * @see TagFactory
 * @package Tests\Service
 */
class TagFactoryTest extends WolnosciowiecTestCase
{
    /**
     * @see TagFactory::createTag()
     */
    public function testCreateTag()
    {
        /** @var TagFactoryInterface $factory */
        $factory = $this->app->offsetGet('factory.tag');
        $tag     = $factory->createTag('user.avatar');

        $this->assertSame('user.avatar', $tag->getName());
        $this->assertSame(date('Y-m-d'), $tag->getDateAdded()->format('Y-m-d'));
    }
}
