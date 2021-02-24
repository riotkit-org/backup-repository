<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Factory;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Factory\JWTFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class LexikJWTFactory implements JWTFactory
{
    private JWTEncoderInterface $encoder;

    public function __construct(JWTEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function createForUser(User $user, array $permissions = null, int $ttl = 86400 * 365 * 2): string
    {
        return $this->encoder->encode([
            'email'       => $user->getEmail()->getValue(),
            'roles'       => $permissions ? $permissions : $user->getPermissions(),
            'exp'         => time() + $ttl
        ]);
    }

    public function createArrayFromToken(string $token): array
    {
        return $this->encoder->decode($token);
    }
}
