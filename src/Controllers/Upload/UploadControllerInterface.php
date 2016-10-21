<?php

namespace Controllers\Upload;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface UploadControllerInterface
{
    /**
     * @return Response
     */
    public function uploadAction() : Response;

    /**
     * @param string $protocolName
     * @return bool
     */
    public function supportsProtocol(string $protocolName) : bool;
}