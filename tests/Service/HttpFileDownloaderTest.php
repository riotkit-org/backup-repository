<?php declare(strict_types=1);

namespace Tests\Service;

use Service\HttpFileDownloader;
use Tests\WolnosciowiecTestCase;

/**
 * @see HttpFileDownloader
 * @package Tests\Service
 */
class HttpFileDownloaderTest extends WolnosciowiecTestCase
{
    /**
     * @return array
     */
    public function provideSaveToData()
    {
        return [
            'Custom mime' => [
                'https://raw.githubusercontent.com/Wolnosciowiec/image-repository/master/README.md',
                ['text/plain'],
                1024 * 1024 * 1024,
                sys_get_temp_dir() . '/test-readme.md',
                'text/plain',
                '',
            ],

            'Image' => [
                'https://github.com/Wolnosciowiec/image-repository/raw/master/docs/images/anarchosyndicalism.png',
                ['image/png'],
                1024 * 1024 * 1024,
                sys_get_temp_dir() . '/anarchosyndicalism.png',
                'image/png',
                '',
            ],

            'Invalid mime type' => [
                'https://raw.githubusercontent.com/Wolnosciowiec/image-repository/master/README.md',
                ['image/jpeg'],
                1024 * 1024 * 1024,
                sys_get_temp_dir() . '/test-readme.md',
                'text/plain',
                'Stream is of unsupported mime type "text/plain", allowed mimes: image/jpeg',
            ],

            'Size limit exceeded' => [
                'https://raw.githubusercontent.com/Wolnosciowiec/image-repository/master/README.md',
                ['text/plain'],
                2, // 2 bytes
                sys_get_temp_dir() . '/test-readme.md',
                'text/plain',
                'Max file size of 2 bytes exceeded',
            ],
        ];
    }

    /**
     * @dataProvider provideSaveToData()
     * @see          HttpFileDownloader::saveTo()
     * @group integration
     *
     * @param string $url
     * @param array|null $mimes
     * @param int|null $limit
     * @param string $destinationPath
     * @param string $expectedMimeType
     * @param string $expectedExceptionMessage
     */
    public function testSaveTo(
        string $url,
        $mimes,
        $limit,
        string $destinationPath,
        string $expectedMimeType,
        string $expectedExceptionMessage = ''
    ) {
        /** @var HttpFileDownloader|callable $downloader */
        $downloader = $this->app->offsetGet('service.http_file_downloader');
        $downloader = $downloader($url);

        $downloader->setAllowedMimes($mimes);
        $downloader->setMaxFileSizeLimit($limit);

        try {
            $savedFile = $downloader->saveTo($destinationPath);
        } catch (\Exception $e) {
            $this->assertSame($expectedExceptionMessage, $e->getMessage());
            return;
        }

        $this->assertContains($expectedMimeType, $savedFile->getFileMimeType());
        $this->assertSame($savedFile->getFileName(), $destinationPath);
    }
}