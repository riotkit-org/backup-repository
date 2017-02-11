<?php declare(strict_types=1);

namespace Tests\Seeders;

use Silex\Application;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @package Tests\Seeders
 */
trait BaseSeeder
{
    /**
     * @return Application|HttpKernelInterface` a
     */
    abstract public function getApp();
}