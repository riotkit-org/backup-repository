<?php declare(strict_types=1);

namespace Actions\Finder;

use Actions\AbstractBaseAction;
use Model\Entity\File;
use Model\Entity\Tag;
use Model\Request\SearchQueryPayload;
use Repository\Domain\FileRepositoryInterface;

/**
 * @package Actions\Registry
 */
class FindAction extends AbstractBaseAction
{
    /**
     * @var SearchQueryPayload $payload
     */
    private $payload;

    /**
     * @var FileRepositoryInterface $fileRepository
     */
    private $fileRepository;

    /**
     * @param FileRepositoryInterface $fileRepository
     */
    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @param File[] $files
     * @return array
     */
    private function remapFilesToResults(array $files)
    {
        $results = [];

        foreach ($files as $file) {
            $results[$file->getFileName()] = [
                'name'         => $file->getFileName(),
                'content_hash' => $file->getContentHash(),
                'mime_type'    => $file->getMimeType(),
                'tags'         => array_map(function (Tag $tag) { return $tag->getName(); }, $file->getTags()->toArray()),
                'date_added'   => $file->getDateAdded(),
            ];
        }

        return $results;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $files = $this->fileRepository->findByQuery(
            $this->payload->getTags(),
            '',
            $this->payload->getLimit(),
            $this->payload->getOffset()
        );

        return [
            'success'     => true,
            'results'     => $this->remapFilesToResults($files['results']),
            'max_results' => $files['max'],
            'pages'       => $files['max'] > 0 ? ceil($files['max'] / $this->payload->getLimit()) : 0,
        ];
    }

    /**
     * @param SearchQueryPayload $payload
     * @return FindAction
     */
    public function setPayload(SearchQueryPayload $payload): FindAction
    {
        $this->payload = $payload;
        return $this;
    }
}
