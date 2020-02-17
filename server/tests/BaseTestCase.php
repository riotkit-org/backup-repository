<?php declare(strict_types=1);

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected function setProtectedProperty(object $object, string $propName, string $propValue): void
    {
        $ref = new \ReflectionClass(\get_class($object));
        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);
        $prop->setValue($object, $propValue);
    }
}
