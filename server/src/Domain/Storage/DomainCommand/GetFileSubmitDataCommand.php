<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\SubmitDataTypes;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\ValueObject\Filename;

class GetFileSubmitDataCommand implements CommandHandler
{
    /**
     * @var FileRepository
     */
    private $repository;

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     *
     * @see Bus::GET_ENTITY_SUBMIT_DATA
     */
    public function handle($input, string $path)
    {
        $fileName = new Filename($input['fileName'] ?? '');
        $file     = $this->repository->findByName($fileName);

        if (!$file) {
            return [null, SubmitDataTypes::TYPE_FILE];
        }

        return [UploadForm::createFromFile($file)->toArray(), SubmitDataTypes::TYPE_FILE];
    }

    public function supportsInput($input, string $path): bool
    {
        return ($input['type'] ?? '') === SubmitDataTypes::TYPE_FILE;
    }

    /**
     * @return array
     */
    public function getSupportedPaths(): array
    {
        return [Bus::GET_ENTITY_SUBMIT_DATA];
    }
}
