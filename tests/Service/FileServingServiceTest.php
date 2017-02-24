<?php declare(strict_types=1);

namespace Tests\Service;

use Service\FileServingService;
use Tests\WolnosciowiecTestCase;

/**
 * @see FileServingService
 */
class FileServingServiceTest extends WolnosciowiecTestCase
{
    /**
     * @return FileServingService
     */
    private function getService()
    {
        return $this->getApp()->offsetGet('service.file.serve');
    }

    /**
     * @see FileServingService::buildClosure()
     */
    public function testBuildClosure()
    {
        $tempFilePath = $this->createTemporaryFile($this->getName(), 'test');

        $closure = $this->getService()->buildClosure($tempFilePath);

        ob_start();
        $closure();
        $contents = ob_get_contents();
        ob_end_clean();

        $this->assertSame('test', $contents);
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\FileNotFoundException
     */
    public function testFailureBuildClosure()
    {
        $this->getService()->buildClosure('/this-path-does-not-exists-or-is-not-writable-or-both');
    }

    /**
     * @see FileServingService::buildOutputHeaders()
     * @return array
     */
    public function testBuildOutputHeaders(): array
    {
        $tempFilePath = $this->createTemporaryFile($this->getName(), 'test');
        $headers = $this->getService()->buildOutputHeaders($tempFilePath);

        $this->assertArrayHasKey('ETag', $headers);
        $this->assertArrayHasKey('Last-Modified', $headers);
        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);

        $this->assertSame(filesize($tempFilePath), $headers['Content-Length']);
        $this->assertSame('text/plain', $headers['Content-Type']);

        return [
            'headers'   => $headers,
            'file_path' => $tempFilePath,
        ];
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\FileNotFoundException
     */
    public function testFailureBuildOutputHeaders()
    {
        $this->getService()->buildOutputHeaders('/this-path-does-not-exists-or-is-not-writable-or-both');
    }

    /**
     * @depends testBuildOutputHeaders
     * @param array $data
     */
    public function testShouldServe(array $data)
    {
        $this->assertFalse(
            $this->getService()->shouldServe($data['file_path'], $data['headers']['Last-Modified'], $data['headers']['ETag'])
        );

        $this->assertTrue(
            $this->getService()->shouldServe($data['file_path'], $data['headers']['Last-Modified'], '')
        );

        $this->assertTrue(
            $this->getService()->shouldServe($data['file_path'], '', '')
        );
    }

    /**
     * @expectedException \Symfony\Component\Filesystem\Exception\FileNotFoundException
     */
    public function testFailureShouldServe()
    {
        $this->getService()->shouldServe('/this-path-does-not-exists-or-is-not-writable-or-both', '', '');
    }
}
