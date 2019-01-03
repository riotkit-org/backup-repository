<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Storage\ActionHandler\DeleteFileHandler;
use App\Domain\Storage\Form\DeleteFileForm;
use App\Domain\Storage\Security\ManagementSecurityContext;

class DeleteCommand implements CommandHandler
{
    /**
     * @var DeleteFileHandler
     */
    private $handler;

    public function __construct(DeleteFileHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param mixed $input
     *
     * @return bool|mixed
     *
     * @throws \App\Domain\Storage\Exception\AuthenticationException
     * @throws \App\Domain\Storage\Exception\StorageException
     */
    public function handle($input)
    {
        if (!isset($input['form']['filename'])) {
            throw new \InvalidArgumentException('Missing form.filename');
        }

        $form = new DeleteFileForm();
        $form->filename = $input['form']['filename'] ?? '';
        $form->password = '';

        $securityContext = new ManagementSecurityContext(
            // can delete all files without knowing the password
            true,

            // password can be empty
            ''
        );

        return (bool) $this->handler->handle($form, $securityContext);
    }

    /**
     * @return array
     */
    public function getSupportedPaths(): array
    {
        return [
            Bus::STORAGE_DELETE
        ];
    }
}
