<?php declare(strict_types=1);

namespace Controllers\Download;

use Controllers\AbstractBaseController;
use Domain\Service\FileServingServiceInterface;
use Manager\Domain\TokenManagerInterface;
use Manager\StorageManager;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @package Controllers\Upload
 */
class FileServeController extends AbstractBaseController
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
        /**
         * @var StorageManager $manager
         * @var FileServingServiceInterface $fileServe
         */
        $manager = $this->getContainer()->offsetGet('manager.storage');
        $fileServe = $this->getContainer()->offsetGet('service.file.serve');

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
            $shouldServe = $fileServe->shouldServe(
                $storagePath,
                $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? null,
                $_SERVER['HTTP_IF_NONE_MATCH'] ?? null
            );

            if ($shouldServe) {
                return new StreamedResponse(
                    $fileServe->buildClosure($storagePath),
                    200,
                    $fileServe->buildOutputHeaders($storagePath)
                );
            }

            return new Response('', 304);
        }

        return new JsonResponse([
            'success' => false,
            'code'    => 404,
            'message' => 'Image not found in the registry',
        ], 404);
    }
}
