<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Factory\FileNameFactory;
use App\Domain\Storage\Factory\PublicUrlFactory;
use App\Domain\Storage\Form\UploadByPostForm;
use App\Domain\Storage\Form\UploadByUrlForm;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Provider\UserUploadProvider;
use App\Domain\Storage\Repository\StagingAreaRepository;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Stream;
use App\Domain\Storage\ValueObject\Url;

/**
 * Provide an URL to add file to the library
 */
class UploadFileByPostHandler extends AbstractUploadHandler
{
    /**
     * @var UserUploadProvider
     */
    private $uploadProvider;

    public function __construct(
        StorageManager $storageManager,
        FileNameFactory $namingFactory,
        PublicUrlFactory $publicUrlFactory,
        UserUploadProvider $uploadProvider,
        SecurityContextFactory $securityContextFactory,
        StagingAreaRepository $stagingAreaRepository
    ) {
        $this->uploadProvider = $uploadProvider;

        parent::__construct(
            $storageManager,
            $namingFactory,
            $publicUrlFactory,
            $securityContextFactory,
            $stagingAreaRepository
        );
    }

    /**
     * @param UploadByPostForm $form
     *
     * @return Filename
     */
    protected function createFileName($form): Filename
    {
        return $this->nameFactory->fromForm($form);
    }

    /**
     * @param UploadByPostForm $form
     *
     * @return Filename
     */
    protected function getRequestedFilename($form): Filename
    {
        return new Filename($form->fileName);
    }

    /**
     * @param UploadByUrlForm $form
     *
     * @return Stream
     */
    protected function createStream($form): Stream
    {
        return $this->uploadProvider->getStreamFromHttp();
    }
}
