<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GrantUserToCollectionController extends ManageCollectionAccessControlController
{
    /**
     * Grant token to be able to operate on a collection
     *
     * @param Request $request
     * @param string $id
     * @param string $uid
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $id, string $uid = ''): Response
    {
        return parent::handleAction($request, $id, $uid);
    }
}
