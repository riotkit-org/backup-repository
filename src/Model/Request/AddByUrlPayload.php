<?php declare(strict_types=1);

namespace Model\Request;

/**
 * @package Model\Request
 */
class AddByUrlPayload
{
    /**
     * @var string $fileUrl
     */
    private $fileUrl = '';

    /**
     * @param string $fileUrl
     * @return AddByUrlPayload
     */
    public function setFileUrl(string $fileUrl)
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

}