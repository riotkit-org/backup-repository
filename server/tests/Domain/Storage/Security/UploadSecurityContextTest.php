<?php declare(strict_types=1);

namespace Tests\Domain\Storage\Security;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\ValueObject\Filesize;
use PHPUnit\Framework\TestCase;

/**
 * @see UploadSecurityContext
 */
class UploadSecurityContextTest extends TestCase
{
    public function provideExampleContexts(): array
    {
        return [
            'Only plaintext files allowed, only one tag, 5000bytes max file size, can set password' => [
                'context' => new UploadSecurityContext(
                    ['food-not-bombs'], // allowed tags
                    false, // can upload any file type
                    true,  // can overwrite
                    5000,  // max file size
                    true,  // enforce token tags
                    false, // enforce no-password
                    false,  // is administrator
                    false,
                    new Token()
                ),
                'booleanExpects' => [
                    'canSetPassword'   => true
                ],
                'mime' => 'text/plain',
                'tag'  => 'food-not-bombs',
                'size' => 4500,
                'expectsMimeIsCorrect' => true,
                'expectsTagIsAllowed'  => true,
                'expectsIsSizeOk'      => true
            ]
        ];
    }

    /**
     * @see UploadSecurityContext::canSetPassword()
     * @see UploadSecurityContext::isTagAllowed()
     * @see UploadSecurityContext::isFileSizeOk()
     *
     * @dataProvider provideExampleContexts
     *
     * @param UploadSecurityContext $context
     * @param array $booleanExpects
     * @param string $mime
     * @param string $tag
     * @param int $size
     * @param bool $expectsMimeIsCorrect
     * @param bool $expectsTagIsAllowed
     * @param bool $expectsIsSizeOk
     */
    public function testBooleanPermissions(
        UploadSecurityContext $context,
        array $booleanExpects,
        string $mime,
        string $tag,
        int $size,
        bool $expectsMimeIsCorrect,
        bool $expectsTagIsAllowed,
        bool $expectsIsSizeOk
    ): void {
        foreach ($booleanExpects as $method => $expectedReturnValue) {
            $this->assertSame($expectedReturnValue, $context->{$method}());
        }

        $this->assertSame($expectsTagIsAllowed,  $context->isTagAllowed($tag));
        $this->assertSame($expectsIsSizeOk,      $context->isFileSizeOk(new Filesize($size)));
    }

    /**
     * When FORM password is not same as password of the file, then we cannot overwrite it
     *
     * @throws \Exception
     */
    public function testCannotOverwriteAFileIfPasswordDoesNotMatch(): void
    {
        $ctx = new UploadSecurityContext(
            ['food-not-bombs'],
            false,
            true,  // can overwrite
            5000,
            true,
            false,
            false,
            false,
            new Token()
        );

        $formWithInvalidPassword = new UploadForm();
        $formWithInvalidPassword->password      = 'this-password-does-not-match-hello-password';
        $formWithInvalidPassword->fileOverwrite = true;

        $formWithMatchingPassword = new UploadForm();
        $formWithMatchingPassword->password      = 'hello';
        $formWithMatchingPassword->fileOverwrite = true;

        $noPasswordForm = new UploadForm();
        $noPasswordForm->fileOverwrite = true;
        $noPasswordForm->password      = '';

        $storedFile = new StoredFile();
        $storedFile->changePassword('hello');

        $noPasswordFile = new StoredFile();

        // for incorrect password
        $this->assertFalse($ctx->canOverwriteFile($storedFile, $formWithInvalidPassword));

        // for valid password
        $this->assertTrue($ctx->canOverwriteFile($storedFile, $formWithMatchingPassword));

        // for no-password form
        $this->assertFalse($ctx->canOverwriteFile($noPasswordFile, $noPasswordForm));
    }

    public function provideCanOverwrite(): array
    {
        return [
            'Allowed to overwrite, but don\'t want actually' => [
                'allowedTo'        => true,
                'wantsToOverwrite' => false,
                'expectedWouldOverwrite' => false
            ],

            'Not allowed to overwrite, but wants' => [
                'allowedTo'        => false,
                'wantsToOverwrite' => true,
                'expectedWouldOverwrite' => false
            ],

            'Not allowed and do not want to' => [
                'allowedTo'        => false,
                'wantsToOverwrite' => false,
                'expectedWouldOverwrite' => false
            ],

            'Allowed to overwrite and wants to overwrite the file' => [
                'allowedTo'        => true,
                'wantsToOverwrite' => true,
                'expectedWouldOverwrite' => true
            ]
        ];
    }

    /**
     * @dataProvider provideCanOverwrite
     *
     * @param bool $allowedTo
     * @param bool $wantsToOverwrite
     * @param bool $expectedWouldOverwrite
     *
     * @throws \Exception
     */
    public function testNotAllowedOrDontWantToOverwrite(
        bool $allowedTo,
        bool $wantsToOverwrite,
        bool $expectedWouldOverwrite
    ): void {
        $ctx = new UploadSecurityContext(
            ['food-not-bombs'],
            false,
            $allowedTo,  // can overwrite
            5000,
            true,
            false,
            false,
            false,
            new Token()
        );

        $dto = new UploadForm();
        $dto->fileOverwrite = $wantsToOverwrite;
        $dto->password      = 'some';

        $storedFile = new StoredFile();
        $storedFile->changePassword('some');

        $this->assertSame($expectedWouldOverwrite, $ctx->canOverwriteFile($storedFile, $dto));
    }
}
