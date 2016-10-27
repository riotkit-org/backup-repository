<?php

namespace Controllers\Upload;

use Actions\Upload\AddByUrlActionHandler;
use Actions\Upload\UploadByHttpActionHandler;
use Controllers\AbstractBaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP/HTTPS handler
 * ==================
 *
 * @package Controllers\Upload
 */
class UploadController extends AbstractBaseController implements UploadControllerInterface
{
    /**
     * @return JsonResponse|Response
     */
    public function uploadAction() : Response
    {
        $action = new UploadByHttpActionHandler(
            (string)$this->getRequest()->get('file_name'),
            (bool)$this->getRequest()->get('file_overwrite'),
            $this->getContainer()->offsetGet('storage.filesize'),
            $this->getContainer()->offsetGet('storage.allowed_types')
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