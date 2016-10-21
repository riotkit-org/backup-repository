<?php

namespace Exception\ImageManager;

class InvalidUrlException extends ImageManagerException
{
    const INVALID_SCHEMA = 'INVALID_SCHEMA';
    const INVALID_SCHEMA_DESCRIPTION = 'Unsupported URL schema';

    public function __construct($code = null, \Exception $previous = null)
    {
        $message = 'Specified URL is invalid';

        if (defined(get_called_class() . '::' . $code . '_DESCRIPTION')) {
            $message = constant(get_called_class() . '::' . $code . '_DESCRIPTION');
        }

        parent::__construct($message, $code, $previous);
    }
}