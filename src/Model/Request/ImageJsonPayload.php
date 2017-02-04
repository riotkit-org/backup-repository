<?php declare(strict_types=1);

namespace Model\Request;

/**
 * @package Model\Request
 */
class ImageJsonPayload
{
    /**
     * @var string $content
     */
    private $content;

    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @var string $mimeType
     */
    private $mimeType;

    /**
     * @return string
     */
    public function getDecodedFileContents(): string
    {
        $parts = explode(';base64,', $this->getContent());

        if (count($parts) !== 2) {
            return '';
        }

        return base64_decode($parts[1]);
    }

    /**
     * @return int
     */
    public function getPayloadSize(): int
    {
        return strlen($this->getDecodedFileContents());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $content
     * @return ImageJsonPayload
     */
    public function setContent(string $content): ImageJsonPayload
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $fileName
     * @return ImageJsonPayload
     */
    public function setFileName(string $fileName): ImageJsonPayload
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @param string $mimeType
     * @return ImageJsonPayload
     */
    public function setMimeType(string $mimeType): ImageJsonPayload
    {
        $this->mimeType = $mimeType;
        return $this;
    }
}