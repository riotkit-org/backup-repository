<?php declare(strict_types=1);

namespace Tests\Domain\Storage\ActionHandler;

use App\Domain\Storage\Aggregate\FileRetrievedFromStorage;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Context\CachingContext;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\Service\AlternativeFilenameResolver;
use App\Domain\Storage\ValueObject\Stream;
use Mockery\MockInterface;
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
            $this->createMock(FilesystemManager::class),
            $this->mockNameResolver()
        ]);

        // replace methods at external services (to test only our scope)
        $securityContext = mock(ReadSecurityContext::class);
        $securityContext->shouldReceive('isAbleToViewFile')->andReturn(false);

        // test it, expectation is that an exception will be thrown
        // because ReadSecurityContext::isAbleToViewFile() returns false
        $handler->handle(
            $this->createExampleForm(),
            $securityContext,
            mock(CachingContext::class)
        );
    }

    /**
     * Assert that returns 304 Not Modified + valid headers in the response when a CachingContext tells that the
     * cache is still valid
     */
    public function testNotModifiedResponse(): void
    {
        $headers = '';

        //
        // mock dependencies
        //
        $securityContext = mock(ReadSecurityContext::class);
        $securityContext->shouldReceive('isAbleToViewFile')->andReturn(true);

        $fromStorage = $this->createMock(StoredFile::class);
        $fromStorage->method('getFilename')->willReturn(new Filename('bakunin.ogv'));
        $file = new FileRetrievedFromStorage($fromStorage, new Stream(fopen('/dev/null', 'rb')));

        $manager = $this->createMock(StorageManager::class);
        $manager->method('retrieve')->willReturn($file);

        $fs = $this->createMock(FilesystemManager::class);
        $fs->method('getFileSize')->willReturn(161);

        //
        // create handler
        //
        $handler = $this->mockHandlerPartially([
            $manager,
            $fs,
            $this->mockNameResolver()
        ]);

        $handler->method('header')->willReturnCallback(function ($header) use (&$headers) { $headers .= $header . " "; });

        // let the CachingContext be saying that the cache is still valid
        $cachingContext = mock(CachingContext::class);
        $cachingContext->shouldReceive('isCacheExpiredForFile')->andReturn(false);

        //
        // test method
        //
        $response = $handler->handle(
            $this->createExampleForm(),
            $securityContext,
            $cachingContext
        );

        $callback = $response->getResponseCallback();
        $callback();

        $this->assertSame(304, $response->getCode());

        $this->assertStringContainsString('Accept-Ranges: bytes', $headers);
        $this->assertStringContainsString('Content-Length: 161', $headers);
        $this->assertStringContainsString('Content-Disposition: attachment; filename="bakunin.ogv"', $headers);
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

    /**
     * @return AlternativeFilenameResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockNameResolver()
    {
        $nameResolver = $this->createMock(AlternativeFilenameResolver::class);
        $nameResolver->method('resolveFilename')->willReturn(new Filename('bakunin.ogv'));

        return $nameResolver;
    }
}
