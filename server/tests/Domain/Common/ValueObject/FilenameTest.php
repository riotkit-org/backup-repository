<?php declare(strict_types=1);

namespace Tests\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonStorageException;
use App\Domain\Common\ValueObject\Filename;
use PHPUnit\Framework\TestCase;

/**
 * @see Filename
 */
class FilenameTest extends TestCase
{
    public function invalidNamesProvider(): array
    {
        return [
            ['/this-is-invalid'],
            [';is not a valid character'],
            ['###']
        ];
    }

    public function correctNamesProvider(): array
    {
        return [
            ['iwa-ait-org-backup.tar.gz'],
            ['amazon-workers-rights-violation.pdf'],
            ['under_score_named_file'],
            ['Bigger-Letters_As_well_as_lower_letters_without_extension']
        ];
    }

    /**
     * @dataProvider invalidNamesProvider
     *
     * @see Filename
     *
     * @param string $name
     */
    public function testValidation_fails(string $name): void
    {
        $this->expectException(CommonStorageException::class);

        new Filename($name);
    }

    /**
     * @dataProvider correctNamesProvider
     *
     * @see Filename
     *
     * @param string $name
     */
    public function testValidation_passes(string $name): void
    {
        $filename = new Filename($name);

        $this->assertSame($name, $filename->getValue());
    }

    /**
     * @see Filename::withSuffix()
     */
    public function testWithSuffix(): void
    {
        $filename = new Filename('zsp-net-pl.sql');
        $withTimestamp = $filename->withSuffix('-2020-01-05');

        $this->assertNotSame($filename, $withTimestamp);
        $this->assertSame('zsp-net-pl.sql', $filename->getValue());
        $this->assertSame('zsp-net-pl-2020-01-05.sql', $withTimestamp->getValue());
    }
}
