<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\SubmitDataTypes;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\ValueObject\Filename;
use ReflectionException;

class GetFileSubmitDataSchemaCommand implements CommandHandler
{
    /**
     * @param mixed $input
     *
     * @param string $path
     * @return mixed
     *
     * @throws ReflectionException
     * 
     * @see Bus::GET_ENTITY_SUBMIT_DATA_SCHEMA
     */
    public function handle($input, string $path)
    {
        return [UploadForm::getFieldsAsJsonSchemaProperties(), SubmitDataTypes::TYPE_FILE];
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
        return [Bus::GET_ENTITY_SUBMIT_DATA_SCHEMA];
    }
}
