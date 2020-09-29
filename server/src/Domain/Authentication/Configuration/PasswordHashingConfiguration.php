<?php declare(strict_types=1);

namespace App\Domain\Authentication\Configuration;

use InvalidArgumentException;

class PasswordHashingConfiguration
{
    public string $algorithm;

    public int $iterations;

    /**
     * @param string $algorithm
     * @param int $iterations
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $algorithm, int $iterations)
    {
        // pre-validation of parameters - will be thrown early
        // @todo: Use this on CompilerPass level to get early validation at container building stage
        if ($iterations >= 100000) {
            throw new InvalidArgumentException('Cannot set more than 100000 iterations of password hashing, it may hang your server');
        }

        if ($iterations < 10000) {
            throw new InvalidArgumentException('Insecure settings, cannot set lower than 10000 iterations of password hashing');
        }

        if (!\in_array($algorithm, hash_algos(), true)) {
            throw new InvalidArgumentException('Hash algorithm "' . $algorithm . '" not supported by your PHP installation');
        }

        $this->algorithm  = $algorithm;
        $this->iterations = $iterations;
    }
}
