<?php

namespace Controllers\Upload;

use Actions\Upload\AddByUrlActionHandler;
use Controllers\AbstractBaseController;
use Model\Request\AddByUrlPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * HTTP/HTTPS handler
 * ==================
 *
 * @package Controllers\Upload
 */
class AddByUrlController extends AbstractBaseController implements UploadControllerInterface
{
    /**
     * @return string
     */
    protected function getPayloadClassName(): string
    {
        return AddByUrlPayload::class;
    }

    /**
     * @return AddByUrlPayload
     */
    protected function getPayload()
    {
        return parent::getPayload();
    }

    /**
     * @return JsonResponse|Response
     */
    public function uploadAction() : Response
    {
        $action = new AddByUrlActionHandler(
            $this->getPayload()->getFileUrl()
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
