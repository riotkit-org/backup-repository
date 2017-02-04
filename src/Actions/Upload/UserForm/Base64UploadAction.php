<?php declare(strict_types=1);

namespace Actions\Upload\UserForm;

use Actions\AbstractBaseAction;
use Model\Request\ImageJsonPayload;

/**
 * @package Actions\Upload\UserForm
 */
class Base64UploadAction extends AbstractBaseAction
{
    /**
     * @var ImageJsonPayload $payload
     */
    private $payload;

    /**
     * @param ImageJsonPayload $payload
     */
    public function __construct(ImageJsonPayload $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return ImageJsonPayload
     */
    private function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $tempFileName = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tempFileName, $this->getPayload()->getDecodedFileContents());

        $_FILES['upload'] = [
            'name'     => $this->getPayload()->getFileName(),
            'error'    => null,
            'tmp_name' => $tempFileName,
            'size'     => $this->getPayload()->getPayloadSize(),
            'type'     => $this->getPayload()->getMimeType(),
        ];

        return [
            'tempFileName' => $tempFileName,
            'fileName'     => $this->getPayload()->getFileName(),
        ];
    }
}