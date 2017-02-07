<?php declare(strict_types=1);

namespace Tests\Service;

use Service\Versioning;
use Tests\WolnosciowiecTestCase;

/**
 * @see Versioning
 * @package Tests\Service
 */
class VersioningTest extends WolnosciowiecTestCase
{
    public function testGetVersion()
    {
        /** @var Versioning $versioning */
        $versioning = $this->app->offsetGet('versioning');

        $this->assertRegExp('/([0-9\.]+)/', $versioning->getVersion());
        $this->assertInternalType('string', $versioning->getReleaseNumber());
        $this->assertInternalType('float', $versioning->getVersionNumber());
    }
}
