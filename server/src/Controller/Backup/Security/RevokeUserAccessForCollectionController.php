<?php declare(strict_types=1);

namespace App\Controller\Backup\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RevokeUserAccessForCollectionController extends ManageCollectionAccessControlController
{
    /**
     * Revoke a token from access to collection
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
