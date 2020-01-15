<?php declare(strict_types=1);

namespace App\Domain\Replication\Factory;

use App\Domain\Common\Factory\UrlFactory;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Replication\DTO\RepositoryLegend;

class RepositoryLegendFactory
{
    /**
     * @var UrlFactory
     */
    private $urlFactory;

    public function __construct(UrlFactory $urlFactory)
    {
        $this->urlFactory = $urlFactory;
    }

    public function createLegend(BaseUrl $baseUrl): RepositoryLegend
    {
        $tokenParam = '?_token=%token%';

        return new RepositoryLegend(
            $baseUrl->getValue() .
                str_replace('FILE_ID', '%file_id',$this->urlFactory->generate('replication.files.submitdata.fetch', ['fileName' => 'FILE_ID'])) .
                $tokenParam,
            $baseUrl->getValue() .
                str_replace('FILE_ID', '%file_id', $this->urlFactory->generate('replication.files.submitdata.fetch', ['fileName' => 'FILE_ID'])) .
                $tokenParam
        );
    }
}
