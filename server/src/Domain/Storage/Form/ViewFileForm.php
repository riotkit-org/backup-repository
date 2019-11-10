<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

class ViewFileForm extends BasicFileAccessForm
{
    /**
     * Bytes range in HTTP format
     *
     * @var string
     */
    public $bytesRange;
}
