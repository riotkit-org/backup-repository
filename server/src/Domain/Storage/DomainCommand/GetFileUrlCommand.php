<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Storage\Factory\PublicUrlFactory;
use App\Domain\Storage\ValueObject\Filename;

class GetFileUrlCommand implements CommandHandler
{
    /**
     * @var PublicUrlFactory
     */
    private $factory;

    public function __construct(PublicUrlFactory $factory)
    {
        $this->factory = $factory;
    }

    public function handle($input, string $path)
    {
        $filename = $input[0] ?? null;
        $baseUrl  = $input[1] ?? null;

        if (!$filename instanceof \App\Domain\Common\ValueObject\Filename || !$baseUrl instanceof BaseUrl) {
            throw new \LogicException('GetFileUrlCommand takes in order Filename and BaseUrl as arguments');
        }

        return $this->factory->fromFilename(Filename::createFromBasicForm($filename), $baseUrl);
    }

    public function supportsInput($input, string $path): bool
    {
        return true;
    }

    public function getSupportedPaths(): array
    {
        return [
            Bus::STORAGE_GET_FILE_URL
        ];
    }
}
