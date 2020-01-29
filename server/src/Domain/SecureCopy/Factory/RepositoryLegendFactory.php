<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Factory;

// @todo: Use App\Domain\SecureCopy instead of Common
use App\Domain\Common\Factory\UrlFactory;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\SecureCopy\DTO\StreamList\RepositoryLegend;

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
                str_replace('FILE_ID', '%file_id',$this->urlFactory->generate('securecopy.files.submitdata.fetch', ['fileName' => 'FILE_ID'])) .
                $tokenParam,
            $this->baseUrl->getValue() .
                str_replace('FILE_ID', '%file_id', $this->urlFactory->generate('securecopy.files.submitdata.fetch', ['fileName' => 'FILE_ID'])) .
                $tokenParam,
            $remainingSince
        );
    }
}
