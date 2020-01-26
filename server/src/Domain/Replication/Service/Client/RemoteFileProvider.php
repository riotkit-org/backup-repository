<?php declare(strict_types=1);

namespace App\Domain\Replication\Service\Client;

interface RemoteFileProvider
{
    /**
     * @param string $fileName
     *
     * @return resource
     */
    public function fetch(string $fileName);
}