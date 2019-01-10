<?php declare(strict_types=1);

namespace App\Controller\MinimumUi;

use App\Controller\BaseController;
use App\Domain\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadImageController extends BaseController
{
    public function handle(Request $request): Response
    {
        $backUrl = $request->get('back');
        $token   = $this->getLoggedUserToken();

        return $this->render('minimumui/ImageUpload.html.twig', [
            'tokenId'           => $token->getId(),
            'backUrl'           => $backUrl,
            'aspectRatio'       => $request->get('ratio') ? abs((float) $request->get('ratio', 16/9)) : null,
            'passwordIsAllowed' => !$token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_NO_PASSWORD),
            'tags'              => $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_TOKEN_TAGS) ? [] : $token->getTags()
        ]);
    }
}
