<?php declare(strict_types=1);

namespace App\Domain\Common\Factory;

interface UrlFactory
{
    /**
     * @param string $routeName
     * @param array $vars
     *
     * @return string
     */
    public function generate(string $routeName, array $vars): string;
}
