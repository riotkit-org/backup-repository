<?php declare(strict_types=1);

namespace Tests\Domain\Storage\ActionHandler;

use App\Domain\Storage\Aggregate\FileRetrievedFromStorage;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\ValueObject\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see ViewFileHandler
 */
class ViewFileHandlerTest extends TestCase
{
    /**
     * @see ViewFileHandler::handle()
     */
    public function testNoPermissions(): void
    {
        $this->expectException(AuthenticationException::class);

        $handler = $this->mockHandlerPartially([
            $this->createMock(StorageManager::class),
            $this->createMock(FilesystemManager::class)
        ]);

        // replace methods at external services (to test only our scope)
        $securityContext = $this->createMock(ReadSecurityContext::class);
        $securityContext->method('isAbleToViewFile')->willReturn(false);

        // test it, expectation is that an exception will be thrown
        // because ReadSecurityContext::isAbleToViewFile() returns false
        $handler->handle(
            $this->createExampleForm(),
            $securityContext
        );
    }

    /**
     * Assert that basic headers are at it's place
     */
    public function testHeadersReturnedValidLengthAndContentDisposition(): void
    {
        $headers = '';

        //
        // mock dependencies
        //
        $securityContext = $this->createMock(ReadSecurityContext::class);
        $securityContext->expects($this->once())->method('isAbleToViewFile')->willReturn(true);

        $fromStorage = $this->createMock(StoredFile::class);
        $fromStorage->method('getFilename')->willReturn(new Filename('bakunin.ogv'));
        $file = new FileRetrievedFromStorage($fromStorage, new Stream(fopen('/bin/sh', 'rb')));

        $manager = $this->createMock(StorageManager::class);
        $manager->method('retrieve')->willReturn($file);

        $fs = $this->createMock(FilesystemManager::class);
        $fs->method('getFileSize')->willReturn(161);

        //
        // create handler
        //
        $handler = $this->mockHandlerPartially([$manager, $fs]);

        $handler->method('header')->willReturnCallback(function ($header) use (&$headers) { $headers .= $header . " "; });

        //
        // test method
        //
        $response = $handler->handle(
            $this->createExampleForm(),
            $securityContext,
        );

        $headers = $response->getHeaders();
        $headersAsString = '';

        foreach ($headers as $header => $value) {
            $headersAsString .= $header . ': ' . $value . "\n";
        }

        $this->assertSame(200, $response->getCode());
        $this->assertStringContainsString('Accept-Ranges: bytes', $headersAsString);
        $this->assertStringContainsString('Content-Length: 161', $headersAsString);
        $this->assertStringContainsString('Content-Disposition: attachment; filename="bakunin.ogv"', $headersAsString);
    }

    private function createExampleForm(): ViewFileForm
    {
        $form = new ViewFileForm();
        $form->filename   = 'bakunin.ogv';
        $form->bytesRange = '';
        $form->password   = '';

        return $form;
    }

    /**
     * @param array $dependencies
     *
     * @return MockObject|ViewFileHandler
     */
    private function mockHandlerPartially(array $dependencies)
    {
        $builder = $this->getMockBuilder(ViewFileHandler::class);
        $builder->setConstructorArgs($dependencies);
        $builder->setMethods(['header']);

        return $builder->getMock();
    }
}
