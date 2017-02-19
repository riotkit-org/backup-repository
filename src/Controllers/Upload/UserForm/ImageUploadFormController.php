<?php

namespace Controllers\Upload\UserForm;

use Actions\Upload\UserForm\Base64UploadAction;
use Controllers\AbstractBaseController;
use Controllers\Upload\UploadController;
use Exception\Upload\UploadException;
use GuzzleHttp\Psr7\Response;
use Model\Entity\Token;
use Model\Permissions\Roles;
use Model\Request\ImageJsonPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * User form handler: Image upload
 * ===============================
 *
 * @package Controllers\Upload
 */
class ImageUploadFormController extends AbstractBaseController
{
    /**
     * @return string
     */
    public function showFormAction(): string
    {
        return $this->getRenderer()->render('@app/ImageUpload.html.twig', [
            'tokenId' => $this->getRequest()->get('_token'),
            'backUrl' => $this->getRequest()->get('back_url'),
            'aspectRatio' => $this->getAspectRatio((float)$this->getRequest()->get('aspectRatio'))
        ]);
    }

    /**
     * @return array
     */
    public function getRequiredRoleNames(): array
    {
        return [Roles::ROLE_UPLOAD_IMAGES];
    }

    /**
     * Normalize input aspect ratio
     *
     * @param float $ratio
     * @return float
     */
    protected function getAspectRatio(float $ratio): float
    {
        if ($ratio < 0.5 || $ratio > 3.5) {
            return 16 / 9;
        }

        return $ratio;
    }

    /**
     * @return string
     */
    protected function getPayloadClassName(): string
    {
        return ImageJsonPayload::class;
    }

    /**
     * @return ImageJsonPayload
     */
    protected function getPayload()
    {
        return parent::getPayload();
    }

    /**
     * This upload action will emulate the request
     * and push it to the regular upload controller
     *
     * @return Response
     */
    public function uploadAction()
    {
        // this will mock our request
        $userUploadAction = new Base64UploadAction($this->getPayload());
        $result           = $userUploadAction->execute();

        // use a regular upload controller
        $request = new Request(
            [
                '_token'    => $this->getRequest()->get('_token'),
                'file_name' => $result['fileName'],
                'tags'      => $this->getToken() instanceof Token
                    ? $this->getToken()->getTags()
                    : [],
            ]
        );

        try {
            $uploadController = new UploadController($this->getContainer(), true);
            $uploadController->setStrictUploadMode(false);
            $uploadController->setRequest($request);
            $uploadController->setAllowedMimeTypes([
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ]);

            return $uploadController->uploadAction();

        } catch (UploadException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Upload failed',
                'details' => $e->getMessage(),
            ], 400);
        }
    }
}
