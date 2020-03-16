<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

use Psr\Http\Message\StreamInterface;

class ViewFileForm extends BasicFileAccessForm
{
    /**
     * Bytes range in HTTP format
     *
     * @var string
     */
    public $bytesRange;
}
