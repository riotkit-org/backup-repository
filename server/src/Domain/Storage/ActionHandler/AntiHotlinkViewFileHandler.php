<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Response\AntiHotlinkResponse;
use App\Domain\Storage\Service\AlternativeFilenameResolver;
use App\Domain\Storage\Service\HotlinkPatternResolver;
use App\Domain\Storage\ValueObject\Filename;

class AntiHotlinkViewFileHandler
{
    /**
     * @var HotlinkPatternResolver
     */
    private $resolver;

    /**
     * @var FileRepository
     */
    private $repository;

    /**
     * @var AlternativeFilenameResolver
     */
    private $mappingResolver;

    public function __construct(
        HotlinkPatternResolver $patternResolver,
        FileRepository $repository,
        AlternativeFilenameResolver $mappingResolver
    ) {
        $this->resolver        = $patternResolver;
        $this->repository      = $repository;
        $this->mappingResolver = $mappingResolver;
    }

    public function handle(
        string $accessToken,
        string $filename,
        array $headers,
        array $query,
        array $server,
        ?int $expirationTime
    ): AntiHotlinkResponse {

        if (!$this->resolver->resolve($accessToken, $filename, $headers, $query, $server, $expirationTime)) {
            return AntiHotlinkResponse::createNoAccessResponse();
        }

        $file = $this->repository->findByName($this->mappingResolver->resolveFilename(new Filename($filename)));

        if (!$file) {
            return AntiHotlinkResponse::createNotFoundResponse();
        }

        return AntiHotlinkResponse::createValidResponse($file->getFilename());
    }
}
