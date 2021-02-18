<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Storage\ActionHandler\UploadFileByPostHandler;
use App\Domain\Storage\Form\UploadByPostForm;
use App\Domain\Storage\ValueObject\Filename;

class UploadCommand implements CommandHandler
{
    private UploadFileByPostHandler $handler;

    public function __construct(UploadFileByPostHandler $handler)
    {
        $this->handler = $handler;
    }

    public function handle($input, string $path)
    {
        $form = new UploadByPostForm();
        $form->tags            = $input['form']['tags'];
        $form->fileName        = (new Filename($input['form']['fileName']))->getValue();
        $form->isFinalFilename = $input['form']['isFinalFilename'] ?? false;
        $form->stream          = $input['form']['stream'] ?? null;

        return \json_decode(
            \json_encode($this->handler->handle($form, $input['user'], $input['accessToken']), JSON_THROW_ON_ERROR, 512),
            true, 512, JSON_THROW_ON_ERROR
        );
    }

    public function supportsInput($input, string $path): bool
    {
        return true;
    }

    public function getSupportedPaths(): array
    {
        return [Bus::STORAGE_UPLOAD];
    }
}
