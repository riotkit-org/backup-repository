<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Static validation for JWT configuration - validates related environment variables on container warmup
 */
class JWTPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $passphrase    = $_SERVER['JWT_PASSPHRASE'] ?? $_ENV['JWT_PASSPHRASE'] ?? '';
        $secretKeyPath = $_SERVER['JWT_SECRET_KEY'] ?? $_ENV['JWT_SECRET_KEY'] ?? '';
        $publicKeyPath = $_SERVER['JWT_PUBLIC_KEY'] ?? $_ENV['JWT_PUBLIC_KEY'] ?? '';
        $lifetime      = $_SERVER['JWT_LIFETIME'] ?? $_ENV['JWT_LIFETIME'] ?? '';

        foreach ([&$passphrase, &$secretKeyPath, &$publicKeyPath, &$lifetime] as &$var) {
            if (preg_match_all('/%([a-z\._0-9]+)%/', $var, $matches)) {
                foreach ($matches[1] as $match) {
                    $var = str_replace('%' . $match . '%', $container->getParameter($match), $var);
                }
            }
        }

        if (!$passphrase) {
            throw new \InvalidArgumentException('JWT_PASSPHRASE needs to be defined');
        }

        if (!is_file($secretKeyPath)) {
            throw new \InvalidArgumentException('JWT_SECRET_KEY not defined or the file does not exist (value=' . $secretKeyPath . ')');
        }

        if (!is_file($publicKeyPath)) {
            throw new \InvalidArgumentException('JWT_PUBLIC_KEY not defined or the file does not exist (value=' . $publicKeyPath . ')');
        }

        if (!$lifetime) {
            throw new \InvalidArgumentException('JWT_LIFETIME needs to be defined, example value: "+1 hour"');
        }

        try {
            $lifetimeExample = new \DateTime($lifetime);
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException('JWT_LIFETIME has invalid format, ' . $exception->getMessage());
        }

        if ($lifetimeExample->getTimestamp() < time()) {
            throw new \InvalidArgumentException('JWT_LIFETIME cannot be pointing to a negative value');
        }
    }
}
