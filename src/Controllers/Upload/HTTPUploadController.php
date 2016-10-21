<?php

namespace Controllers\Upload;

use Actions\Upload\UploadActionHandler;
use Controllers\AbstractBaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP/HTTPS handler
 * ==================
 *
 * @package Controllers\Upload
 */
class HTTPUploadController extends AbstractBaseController implements UploadControllerInterface
{
    /**
     * @return JsonResponse|Response
     */
    public function uploadAction() : Response
    {
        $action = new UploadActionHandler(
            (string)$this->getRequest()->request->get('image_file_url')
        );
        $action->setContainer($this->getContainer())
            ->setController($this);

        return new JsonResponse($action->execute());
    }

    /**
     * @inheritdoc
     */
    public function supportsProtocol(string $protocolName) : bool
    {
        return in_array($protocolName, ['http', 'https']);
    }
}