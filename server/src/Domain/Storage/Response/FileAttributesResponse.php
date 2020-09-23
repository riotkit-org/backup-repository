<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use App\Domain\Common\Http;
use App\Domain\Common\Response\NormalResponse;
use App\Domain\Storage\Entity\StoredFile;

class FileAttributesResponse extends NormalResponse implements \JsonSerializable
{
    /**
     * @var string[] $attributes
     */
    protected array $attributes = [];

    public static function createSuccessResponse(StoredFile $storedFile): FileAttributesResponse
    {
        $response = new static();
        $response->httpCode = Http::HTTP_OK;
        $response->status   = true;
        $response->message  = 'Attributes found';

        foreach ($storedFile->getAttributes() as $attribute) {
            $response->attributes[$attribute->getName()]  = $attribute->getValue();
        }

        return $response;
    }
}
