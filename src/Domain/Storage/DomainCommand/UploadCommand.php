<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Storage\ActionHandler\UploadFileByPostHandler;
use App\Domain\Storage\Form\UploadByPostForm;
use App\Domain\Storage\ValueObject\Filename;

class UploadCommand implements CommandHandler
{
    /**
     * @var UploadFileByPostHandler
     */
    private $handler;

    public function __construct(UploadFileByPostHandler $handler)
    {
        $this->handler = $handler;
    }

    public function handle($input)
    {
        $form = new UploadByPostForm();
        $form->password      = $input['form']['password'];
        $form->tags          = $input['form']['tags'];
        $form->fileName      = (new Filename($input['form']['fileName']))->getValue();
        $form->fileOverwrite = $input['form']['fileOverwrite'];
        $form->backUrl       = $input['form']['backUrl'];
        $form->contentIdent  = $input['form']['contentIdent'] ?? '';

        return \json_decode(
            \json_encode(
                $this->handler->handle($form, $input['baseUrl'], $input['token'])
            ),
            true
        );
    }

    /**
     * @return array
     */
    public function getSupportedPaths(): array
    {
        return [
            Bus::STORAGE_UPLOAD
        ];
    }
}
