<?php declare(strict_types=1);

namespace Controllers\Download;

use Controllers\AbstractBaseController;
use Manager\Domain\TokenManagerInterface;
use Manager\StorageManager;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package Controllers\Upload
 */
class ImageServeController extends AbstractBaseController
{
    /**
     * Everyone could download images, as those are public
     *
     * @inheritdoc
     */
    public function assertValidateAccessRights(
        Request $request,
        TokenManagerInterface $tokenManager,
        array $requiredRoles = []
    ) {
        return;
    }

    /**
     * @param string|null $imageName
     * @return string
     */
    public function downloadAction($imageName = null)
    {
        /** @var StorageManager $manager */
        $manager = $this->getContainer()->offsetGet('manager.storage');

        try {
            // image_file_url is used to get a file that was submitted using a full external URL
            // instead of file name, using this method we are able to detect if given URL
            // was already mirrored/cached :-)
            $requestedFile = $this->getRequest()->get('image_file_url');
            $storagePath = $requestedFile
                ? $manager->getPathWhereToStoreTheFile($requestedFile)
                : $manager->assertGetStoragePathForFile($imageName);

        } catch (FileNotFoundException $e) {
            $storagePath = '';
        }

        if ($storagePath !== '' && is_file($storagePath)) {
            $fp         = fopen($storagePath, 'r');
            $firstBytes = fread($fp, 1024);
            $mime = $this->getMime($firstBytes);

            @header('Content-Type: ' . $mime);
            @header('Content-Length: ' . filesize($storagePath));

            print($firstBytes);
            fpassthru($fp);
            fclose($fp);
            return '';
        }

        return new JsonResponse([
            'success' => false,
            'code'    => 404,
            'message' => 'Image not found in the registry',
        ], 404);
    }

    /**
     * @param string $bufferedString
     * @return string
     */
    private function getMime($bufferedString): string
    {
        $mime = (new \finfo(FILEINFO_MIME))->buffer($bufferedString);
        $parts = explode(';', (string)$mime);

        return $parts[0];
    }
}
