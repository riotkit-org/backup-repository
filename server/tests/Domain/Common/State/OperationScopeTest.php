<?php declare(strict_types=1);

namespace Tests\Domain\Common\State;

use App\Domain\Common\State\OperationScope;
use PHPUnit\Framework\TestCase;

/**
 * @see OperationScope
 */
class OperationScopeTest extends TestCase
{
    // array $allowedMimesToUpload, bool $allowOverwriting, int $maxFileSize, bool $allowToUpload

    public function provideData(): array
    {
        return [
            'Not allowed to upload at all' => [
                'allowedMimesToUpload' => ['application/json'],
                'allowOverwriting'     => false,
                'maxFileSize'          => 1024,
                'allowToUpload'        => false,

                'expectFileSize'           => 0,
                'expectAllowedToUpload'    => false,
                'expectAllowedToOverwrite' => false,
                'expectAllowedMimes'       => []
            ],

            'Allowed to upload, but not overwriting' => [
                'allowedMimesToUpload' => ['application/json'],
                'allowOverwriting'     => false,
                'maxFileSize'          => 1024,
                'allowToUpload'        => true,

                'expectFileSize'           => 1024,
                'expectAllowedToUpload'    => true,
                'expectAllowedToOverwrite' => false,
                'expectAllowedMimes'       => ['application/json']
            ]
        ];
    }

    /**
     * @see OperationScope
     *
     * @dataProvider provideData
     *
     * @param array $allowedMimesToUpload
     * @param bool $allowOverwriting
     * @param int $maxFileSize
     * @param bool $allowToUpload
     * @param int $expectedFileSize
     * @param bool $expectAllowedToUpload
     * @param bool $expectAllowedToOverwrite
     * @param array $expectAllowedMimes
     */
    public function testThePolicy(
        array $allowedMimesToUpload,
        bool  $allowOverwriting,
        int   $maxFileSize,
        bool  $allowToUpload,

        int   $expectedFileSize,
        bool  $expectAllowedToUpload,
        bool  $expectAllowedToOverwrite,
        array $expectAllowedMimes
    ): void
    {
        $policy = new OperationScope(
            $allowedMimesToUpload,
            $allowOverwriting,
            $maxFileSize,
            $allowToUpload
        );

        $this->assertSame($expectedFileSize, $policy->getAllowedMaxFileSize());
        $this->assertSame($expectAllowedToUpload, $policy->isAllowToUpload());
        $this->assertSame($expectAllowedToOverwrite, $policy->isAllowedToOverwriteFiles());
        $this->assertSame($expectAllowedMimes, $policy->getAllowedMimeTypesToUpload());
    }
}
