<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Factory\FileNameFactory;
use App\Domain\Storage\Factory\PublicUrlFactory;
use App\Domain\Storage\Form\UploadByUrlForm;
use App\Domain\Storage\Provider\HttpDownloadProvider;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Repository\StagingAreaRepository;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Stream;
use App\Domain\Storage\ValueObject\Url;

/**
 * Provide an URL to add file to the library
 */
class UploadFileByUrlHandler extends AbstractUploadHandler
{
    /**
     * @var HttpDownloadProvider
     */
    private $provider;

    public function __construct(
        StorageManager $storageManager,
        FileNameFactory $namingFactory,
        PublicUrlFactory $publicUrlFactory,
        HttpDownloadProvider $provider,
        SecurityContextFactory $securityFactory,
        StagingAreaRepository $stagingAreaRepository
    ) {
        $this->provider = $provider;

        parent::__construct(
            $storageManager,
            $namingFactory,
            $publicUrlFactory,
            $securityFactory,
            $stagingAreaRepository
        );
    }

    /**
     * @param UploadByUrlForm $form
     *
     * @return Filename
     */
    protected function createFileName($form): Filename
    {
        return $this->nameFactory->fromUrl(new Url($form->fileUrl));
    }

    /**
     * @param UploadByUrlForm $form
     *
     * @return Filename
     */
    protected function getRequestedFilename($form): Filename
    {
        return $this->createFileName($form);
    }

    /**
     * @param UploadByUrlForm $form
     *
     * @return Stream
     */
    protected function createStream($form): Stream
    {
        return $this->provider->getStreamFromUrl(new Url($form->fileUrl));
    }
}
