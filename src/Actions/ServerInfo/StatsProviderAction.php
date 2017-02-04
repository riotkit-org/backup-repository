<?php declare(strict_types=1);

namespace Actions\ServerInfo;

use Actions\AbstractBaseAction;

/**
 * @package Actions\ServerInfo
 */
class StatsProviderAction extends AbstractBaseAction
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $storagePath = $this->getContainer()->offsetGet('storage.path');

        return [
            'disk_space' => [
                'free'  => disk_free_space($storagePath),
                'total' => disk_total_space($storagePath),
            ],

            'avg_load' => sys_getloadavg(),

            'storage' => [
                'elements_count' => (count(scandir($storagePath)) - 2),
            ],
        ];
    }
}