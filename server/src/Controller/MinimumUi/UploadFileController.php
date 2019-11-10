<?php declare(strict_types=1);

namespace App\Controller\MinimumUi;

use App\Controller\BaseController;
use App\Domain\Roles;
use function ByteUnits\bytes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadFileController extends BaseController
{
    public function handle(Request $request): Response
    {
        $token = $this->getLoggedUserToken();

        return $this->render('minimumui/FileUpload.html.twig', [
            'tokenId'           => $token->getId(),
            'allowedMimeTypes'  => $token->getAllowedMimeTypes(),
            'maxFileSize'       => $this->fromBytesToHumanReadable($token->getMaxAllowedFileSize()),
            'passwordIsAllowed' => !$token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_NO_PASSWORD),
            'tagsAreEnforced'   => $token->hasRole(Roles::ROLE_UPLOAD_ENFORCE_TOKEN_TAGS),
            'tags'              => $token->getTags(),
            'formOpts'          => $this->getOpts($request),
            'locale'            => $request->getLocale()
        ]);
    }

    /**
     * Decode "opts" parameter from query string and build a HTTP query string from it
     *
     * Example: It is a JSON encoded in base64. Decode it into array, then build a var=value&var2=value (query string),
     *          so we can pass it to an API call
     *
     * @param Request $request
     *
     * @return string
     */
    private function getOpts(Request $request): string
    {
        $optsEncoded = $request->query->get('opts');

        if ($optsEncoded) {
            $decoded = @\json_decode(@\base64_decode($optsEncoded), true);

            if (\is_array($decoded)) {
                return \urldecode(\http_build_query($decoded));
            }
        }

        return '';
    }

    private function fromBytesToHumanReadable(int $bytes): string
    {
        if (!$bytes) {
            return '';
        }

        return bytes($bytes)->format();
    }
}
