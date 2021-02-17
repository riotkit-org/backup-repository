<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

use App\Domain\Authentication\Entity\User;

class AccessTokenListingForm
{
    public ?User $user = null;

    public int $page = 1;

    public int $perPageLimit = 20;
}
