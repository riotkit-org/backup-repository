<?php declare(strict_types=1);

namespace App\Controller\MinimumUi;

use App\Controller\BaseController;
use App\Domain\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadFileController extends BaseController
{
    public function handle(Request $request): Response
    {
        $backUrl = $request->get('back');
        $token   = $this->getLoggedUserToken();

        return $this->render('minimumui/FileUpload.html.twig', [
            'tokenId'           => $token->getId(),
            'allowedMimeTypes'  => $token->getAllowedMimeTypes(),
            'maxFileSize'       => $token->getMaxAllowedFileSize(),
            'backUrl'           => $backUrl,
            'passwordIsAllowed' => !$token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_NO_PASSWORD),
            'tags'              => $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_TOKEN_TAGS) ? [] : $token->getTags()
        ]);
    }
}
