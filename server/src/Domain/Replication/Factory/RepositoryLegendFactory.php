<?php declare(strict_types=1);

namespace App\Domain\Replication\Factory;

// @todo: Use App\Domain\Replication instead of Common
use App\Domain\Common\Factory\UrlFactory;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Replication\DTO\StreamList\RepositoryLegend;

class RepositoryLegendFactory
{
    private UrlFactory $urlFactory;
    private BaseUrl $baseUrl;

    public function __construct(UrlFactory $urlFactory, string $baseUrl)
    {
        $this->urlFactory = $urlFactory;
        $this->baseUrl    = new BaseUrl($baseUrl);
    }

    public function createLegend(int $remainingSince): RepositoryLegend
    {
        $tokenParam = '?_token=%token%';

        return new RepositoryLegend(
            $this->baseUrl->getValue() .
                str_replace('FILE_ID', '%file_id',$this->urlFactory->generate('replication.files.submitdata.fetch', ['fileName' => 'FILE_ID'])) .
                $tokenParam,
            $this->baseUrl->getValue() .
                str_replace('FILE_ID', '%file_id', $this->urlFactory->generate('replication.files.submitdata.fetch', ['fileName' => 'FILE_ID'])) .
                $tokenParam,
            $remainingSince
        );
    }
}
