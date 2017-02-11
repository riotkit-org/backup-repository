<?php declare(strict_types=1);

namespace Tests\Controllers\Finder;

use Tests\Seeders\TaggedFileSeeder;
use Tests\WolnosciowiecTestCase;

/**
 * @see FinderController
 * @package Tests\Controllers\Finder
 */
class FinderControllerTest extends WolnosciowiecTestCase
{
    use TaggedFileSeeder;

    /**
     * @return array
     */
    public function provideQueryData()
    {
        return [
            'Simple query, 1 element from start, one tag' => [
                ['uploads'],
                1, 0,
                '"name":"test.txt"',
            ],

            'Offset moved (next page)' => [
                ['uploads'],
                1, 1,
                '"results":[]',
            ],

            'No tag selected (shows all)' => [
                [],
                1, 0,
                '"name":"test.txt"',
            ],

            'Invalid tag selected' => [
                ['this-tag-does-not-exists'],
                1, 0,
                '"results":[]',
            ],
        ];
    }

    /**
     * @dataProvider provideQueryData()
     *
     * @param array $tags
     * @param int $limit
     * @param int $offset
     * @param string $assertContains
     */
    public function testFindAction(array $tags, int $limit, int $offset, string $assertContains)
    {
        $this->prepareDatabase();
        $this->createTestTaggedFile();

        $client = $this->createClient();
        $client->request(
            'POST',
            '/repository/search/query?_token=' . $this->getAdminToken(),
            [], [], [],
            json_encode([
                'tags'   => $tags,
                'limit'  => $limit,
                'offset' => $offset,
            ])
        );

        $response = $client->getResponse()->getContent();
        $this->assertContains($assertContains, $response);
    }
}
