<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Storage\ActionHandler\DeleteFileHandler;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Form\DeleteFileForm;
use App\Domain\Storage\Security\ManagementSecurityContext;

class DeleteCommand implements CommandHandler
{
    private DeleteFileHandler $handler;

    public function __construct(DeleteFileHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param mixed $input
     * @param string $path
     *
     * @return bool|mixed
     *
     * @throws AuthenticationException
     * @throws StorageException
     */
    public function handle($input, string $path)
    {
        if (!isset($input['form']['filename'])) {
            throw new \InvalidArgumentException('Missing form.filename');
        }

        $form = new DeleteFileForm();
        $form->filename = $input['form']['filename'] ?? '';
        $form->password = '';

        $securityContext = new ManagementSecurityContext(true);

        return (bool) $this->handler->handle($form, $securityContext);
    }

    public function supportsInput($input, string $path): bool
    {
        return true;
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
