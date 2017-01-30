<?php

namespace Controllers\Download;

use Controllers\AbstractBaseController;
use Manager\StorageManager;
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
     * @param Request $request
     * @param string $allowedToken
     */
    public function assertValidateAccessRights(Request $request, $allowedToken)
    {
        return;
    }

    /**
     * @param string|null $imageName
     * @return string
     */
    public function downloadAction($imageName = null)
    {
        /** @var StorageManager $manager */
        $manager       = $this->getContainer()->offsetGet('manager.storage');
        $requestedFile = $this->getRequest()->request->get('image_file_url');
        $storagePath   = $requestedFile
            ? $manager->getPathWhereToStoreTheFile($requestedFile)
            : $manager->assertGetStoragePathForFile($imageName);

        if (is_file($storagePath)) {
            $fp         = fopen($storagePath, 'r');
            $firstBytes = fread($fp, 1024);
            $mime = $this->getMime($firstBytes);

            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($storagePath));

            print($firstBytes);
            fpassthru($fp);
            fclose($fp);
            return '';
        }

        return new JsonResponse([
            'code'    => 404,
            'message' => 'Image not found in the registry',
        ], 404);
    }

    /**
     * @param string $bufferedString
     * @return string
     */
    private function getMime($bufferedString)
    {
        $mime = (new \finfo(FILEINFO_MIME))->buffer($bufferedString);
        $parts = explode(';', (string)$mime);

        return $parts[0];
    }
}