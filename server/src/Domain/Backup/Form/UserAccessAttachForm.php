<?php declare(strict_types=1);

namespace App\Domain\Backup\Form;

class UserAccessAttachForm extends CollectionAddDeleteUserForm
{
    /**
     * @var string[]
     */
    public array $permissions = [];
}
