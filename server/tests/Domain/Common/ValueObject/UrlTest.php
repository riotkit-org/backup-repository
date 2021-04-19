<?php declare(strict_types=1);

namespace Tests\Domain\Common\ValueObject;

use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Common\ValueObject\Url;
use PHPUnit\Framework\TestCase;

/**
 * @see Url
 */
class UrlTest extends TestCase
{
    /**
     * @see Url::withVar()
     */
    public function testWithVar(): void
    {
        $template = new Url('/content/{{ postId }}', new BaseUrl('https://iwa-ait.org'));
        $url = $template->withVar('postId', 'postal-workers-take-action-management-goes-after-zsp');

        $this->assertSame(
            'https://iwa-ait.org/content/postal-workers-take-action-management-goes-after-zsp',
            $url->getValue()
        );
    }
}
