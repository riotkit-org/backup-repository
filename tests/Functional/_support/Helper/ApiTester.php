<?php declare(strict_types=1);

namespace Helper;

use Codeception\Module;

class ApiTester extends Module\REST
{
    use StoreTrait;
    use TemplatingTrait;

    public function _beforeSuite($settings = []): void
    {
        $this->clearTheStore();
    }
}
