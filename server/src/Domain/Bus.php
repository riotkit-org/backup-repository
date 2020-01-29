<?php declare(strict_types=1);

namespace App\Domain;

/**
 * @codeCoverageIgnore
 */
final class Bus
{
    //
    // calls
    //
    public const STORAGE_GET_FILE_SIZE     = 'storage.get_file_size';
    public const STORAGE_UPLOAD            = 'storage.upload';
    public const STORAGE_DELETE            = 'storage.delete';
    public const STORAGE_GET_FILE_URL      = 'storage.get_file_url';

    //
    // cross domain
    //

    // data that can be submitted via File Repository endpoint to add a valid file
    public const GET_ENTITY_SUBMIT_DATA        = 'entity.get_submit_data';

    //
    // events
    //
    public const EVENT_STORAGE_UPLOADED_OK = 'event.storage_uploaded_ok';
}
