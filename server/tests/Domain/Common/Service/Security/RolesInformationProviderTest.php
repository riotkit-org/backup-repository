<?php declare(strict_types=1);

namespace Tests\Domain\Common\Service\Security;

use App\Domain\Common\Service\Security\RolesInformationProvider;
use PHPUnit\Framework\TestCase;

/**
 * @see RolesInformationProvider
 */
class RolesInformationProviderTest extends TestCase
{
    public function testFindAllRolesWithTheirDescription(): void
    {
        $provider = new RolesInformationProvider();
        $result = $provider->findAllRolesWithTheirDescription();

        foreach ($result as $roleName => $roleDescription) {
            $this->assertIsString('string', $roleName);
            $this->assertIsString('string', $roleDescription);
            $this->assertRegExp('/([a-z\.0-9_]+)/', $roleName);
        }

        $this->assertNotEmpty($result);
    }
}
