<?php declare(strict_types=1);

namespace Tests\Manager;

use Manager\StorageManager;
use Model\Entity\File;
use Tests\WolnosciowiecTestCase;

/**
 * @see StorageManager
 * @package Tests\Manager
 */
class StorageManagerTest extends WolnosciowiecTestCase
{
    /**
     * @return StorageManager
     */
    private function getManager()
    {
        return $this->app->offsetGet('manager.storage');
    }

    /**
     * @see StorageManager::getFileUrl()
     */
    public function testGetFileUrl()
    {
        $this->assertSame(
            'http://localhost:8888/public/download/long-live-iwa.png',
            $this->getManager()->getFileUrl((new File())->setFileName('long-live-iwa.png'))
        );
    }

    /**
     * @return array
     */
    public function provideUrls()
    {
        return [
            'Simple with without prefix' => [
                'http://localhost:8888/public/download/long-live-iwa.png',
                'long-live-iwa.png',
                false,
            ],

            'Simple with prefix' => [
                'http://localhost:8888/public/download/long-live-iwa.png',
                '66c8369c-long-live-iwa.png',
                true,
            ],

            'With query string' => [
                'http://localhost:8888/public/download/long-live-iwa.png?test_query_string=1',
                '9a339627-long-live-iwa.png',
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideUrls
     *
     * @param string $url
     * @param string $expectedFileName
     * @param bool   $withPrefix
     */
    public function testGetFileName(string $url, string $expectedFileName, bool $withPrefix)
    {
        $this->assertSame($expectedFileName, $this->getManager()->getFileName($url, $withPrefix));
    }

    /**
     * @dataProvider provideUrls
     * @param string $url
     */
    public function testGetUniquePathWhereToStorageFile(string $url)
    {
        $this->assertTrue(
            !is_file($this->getManager()->getUniquePathWhereToStorageFile($url))
        );
    }

    /**
     * @dataProvider provideUrls
     * @param string $url
     */
    public function testCanWriteFile(string $url)
    {
        $this->assertTrue(
            $this->getManager()->canWriteFile($url)
        );
    }

    /**
     * @see StorageManager::assertGetStoragePathForFile()
     */
    public function testAssertGetStoragePathForFile()
    {
        file_put_contents($this->getManager()->getStoragePath() . '/test.txt', 'test');

        $this->assertTrue(
            is_file($this->getManager()->assertGetStoragePathForFile('test.txt'))
        );

        // clean up
        @unlink($this->getManager()->getStoragePath() . '/test.txt');
    }

    /**
     * @see StorageManager::assertGetStoragePathForFile()
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function testFailureAssertGetStoragePathForFile()
    {
        $this->assertTrue(
            is_file($this->getManager()->assertGetStoragePathForFile('test.txt'))
        );
    }

    public function testGetUrlByName()
    {
        $path = $this->getManager()->getStoragePath() . '/test.txt';
        file_put_contents($path, 'test');

        // file that exists
        $this->assertSame(
            'http://localhost:8888/public/download/test.txt',
            $this->getManager()->getUrlByName($path)
        );

        // file that does not exists
        $this->assertSame(
            'http://localhost:8888/public/download/c24b6a3e-test-image.png',
            $this->getManager()->getUrlByName('test-image.png')
        );

        @unlink($path);
    }
}