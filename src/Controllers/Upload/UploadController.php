<?php

namespace Controllers\Upload;

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
    /** @var bool $strictUploadMode */
    private $strictUploadMode = true;

    /**
     * @return JsonResponse|Response
     */
    public function uploadAction() : Response
    {
        $action = new UploadByHttpActionHandler(
            $this->getContainer()->offsetGet('storage.filesize'),
            $this->getContainer()->offsetGet('storage.allowed_types'),
            $this->getContainer()->offsetGet('manager.storage'),
            $this->getContainer()->offsetGet('manager.file_registry')
        );

        $action->setData(
            (string)$this->getRequest()->get('file_name'),
            (bool)$this->getRequest()->get('file_overwrite')
        );

        $action->setStrictUploadMode($this->isStrictUploadMode());

        return new JsonResponse($action->execute());
    }

    /**
     * @inheritdoc
     */
    public function supportsProtocol(string $protocolName) : bool
    {
        return in_array($protocolName, ['http', 'https']);
    }

    /**
     * @param boolean $strictUploadMode
     * @return UploadController
     */
    public function setStrictUploadMode(bool $strictUploadMode): UploadController
    {
        $this->strictUploadMode = $strictUploadMode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isStrictUploadMode(): bool
    {
        return $this->strictUploadMode;
    }
}