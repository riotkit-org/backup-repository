<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Storage\DTO\SubmitData;
use App\Domain\Storage\Form\UploadByPostForm;
use App\Domain\SubmitDataTypes;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\ValueObject\Filename;

class GetFileSubmitDataCommand implements CommandHandler
{
    private FileRepository $repository;

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $input
     * @param string $path
     *
     * @return null|SubmitData
     *
     * @see Bus::GET_ENTITY_SUBMIT_DATA
     */
    public function handle($input, string $path): ?SubmitData
    {
        $fileName = new Filename($input['fileName'] ?? '');
        $file     = $this->repository->findByName($fileName);

        if (!$file) {
            return null;
        }

        return new SubmitData(
            SubmitDataTypes::TYPE_FILE,
            $file->getFilename()->getValue(),
            $file->getDateAdded(),
            $file->getTimezone(),

            // we want to make possible to re-submit this form later by eg. a mirror server
            UploadByPostForm::createFromFile($file)->toArray()
        );
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
