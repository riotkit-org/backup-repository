<?php declare(strict_types=1);

namespace App\Domain\Storage\Response\Docs;

abstract class FileUploadedResponse
{
    /**
     * @var string
     */
    public $status;

    /**
     * @var int
     */
    public int $error_code;

    /**
     * @var int
     */
    public int $http_code;

    /**
     * @var string
     */
    public string $url;

    /**
     * @var string
     */
    public string $back;

    /**
     * @var string
     */
    public string $id;

    /**
     * @var string
     */
    public string $filename;

    /**
     * @var string
     */
    public string $requested_filename;

    /**
     * @var string[]
     */
    public array $context;
}
