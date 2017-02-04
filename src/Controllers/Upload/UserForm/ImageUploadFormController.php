<?php

namespace Controllers\Upload\UserForm;

use Actions\Upload\UserForm\Base64UploadAction;
use Controllers\AbstractBaseController;
use Controllers\Upload\UploadController;
use Exception\Upload\UploadException;
use GuzzleHttp\Psr7\Response;
use Model\Permissions\Roles;
use Model\Request\ImageJsonPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * User form handler: Image upload
 * ===============================
 *
 * @package Controllers\Upload
 */
class ImageUploadFormController extends AbstractBaseController
{
    /**
     * @var ImageJsonPayload $payload
     */
    private $payload;

    /**
     * @return string
     */
    public function showFormAction()
    {
        return $this->getRenderer()->render('@app/ImageUpload.html.twig', [
            'tokenId' => $this->getRequest()->get('_token'),
            'backUrl' => $this->getRequest()->get('backUrl')
        ]);
    }

    /**
     * @return string
     */
    public function getRequiredRoleName()
    {
        return Roles::ROLE_UPLOAD_IMAGES;
    }

    /**
     * @return ImageJsonPayload
     */
    private function getPayload(): ImageJsonPayload
    {
        if ($this->payload === null) {
            /** @var SerializerInterface $serializer */
            $serializer = $this->getContainer()->offsetGet('serializer');
            $this->payload = $serializer->deserialize($this->getRequest()->getContent(false), ImageJsonPayload::class, 'json');
        }

        return $this->payload;
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
            ]
        );

        try {
            $uploadController = new UploadController($this->getContainer(), true);
            $uploadController->setStrictUploadMode(false);
            $uploadController->setRequest($request);

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
