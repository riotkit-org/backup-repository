<?php declare(strict_types=1);

namespace App\Domain\Storage\Provider;

use App\Domain\Roles;

class MimeTypeProvider
{
    private const MIMES_PER_ROLE = [
        Roles::ROLE_UPLOAD_IMAGES => [
            'image/png', 'image/jpg', 'image/jpeg',
            'image/gif', 'image/webp', 'image/pipeg',
        ],

        Roles::ROLE_UPLOAD_DOCS => [
            'application/vnd.ms-works', 'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.text', 'application/json', 'application/octet-stream', 'text/csv'
        ],

        Roles::ROLE_UPLOAD_BACKUP => [
            'application/octet-stream', 'application/json', 'application/x-bzip', 'application/x-bzip2',
            'application/x-rar-compressed', 'text/plain', 'application/x-tar', 'application/zip', 'application/xml',
            'application/sql', 'text/x-sql', 'text/sql', 'application/x-cpio', 'application/x-shar', 'application/x-lzma',
            'application/x-lzip', 'application/gzip', 'application/x-lzop', 'application/x-compress', 'application/x-xz',
            'application/x-compress', 'application/x-7z-compressed', 'application/x-ace-compressed', 'application/x-arj'
        ]
    ];

    public function getMimesForRole(string $roleName): array
    {
        if (isset(self::MIMES_PER_ROLE[$roleName])) {
            return self::MIMES_PER_ROLE[$roleName];
        }

        return [];
    }
}
