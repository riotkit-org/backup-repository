<?php declare(strict_types=1);

namespace Tests\Domain\Authentication\Manager;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Manager\UserManager;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Service\UuidValidator;
use App\Domain\Authentication\Configuration\PasswordHashingConfiguration;
use Tests\BaseTestCase;

/**
 * @see UserManager
 */
class UserManagerTest extends BaseTestCase
{
    private function getManagerMocked(): UserManager
    {
        return new UserManager(
            $this->createMock(UserRepository::class),
            $this->createMock(UuidValidator::class),
            $this->createMock(PasswordHashingConfiguration::class)
        );
    }

    public function testEditUserExpirationDateCanBeNull(): void
    {
        $user = new User();

        $this->getManagerMocked()->editUser(
            $user,
            [],
            null,
            'Anarchist Federation',
            'As anarchist communists we fight for a world without leaders, where power is shared equally amongst
             communities, and people are free to reach their full potential.',
            []
        );

        $this->assertTrue($user->isNotExpired());
    }

    public function testEditUserSetsValidExpirationDate(): void
    {
        $user    = new User();
        $expDate = (new \DateTime('now'))->modify('+5 days');

        $this->getManagerMocked()->editUser(
            $user,
            [],
            $expDate->format('Y-m-d H:i:s'),
            'Food Not Bombs',
            'Food Not Bombs is a loose-knit group of independent collectives, sharing free vegan and vegetarian 
            food with others. Food Not Bombs\' ideology is that myriad corporate and government priorities are skewed
             to allow hunger to persist in the midst of abundance. To demonstrate this and to reduce costs, a large 
             amount of the food served by the group is surplus food from grocery stores, bakeries and markets that 
             would otherwise go to waste, or occasionally has already been thrown away. This group exhibits a form 
             of franchise activism.',
            []
        );

        $this->assertTrue($user->isNotExpired());
        $this->assertEquals($expDate->format('Y-m-d H:i:s'), $user->getExpirationDate()->format('Y-m-d H:i:s'));
    }
}
