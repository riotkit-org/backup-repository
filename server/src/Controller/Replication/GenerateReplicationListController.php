<?php declare(strict_types=1);

namespace App\Controller\Replication;

use App\Controller\BaseController;
use App\Domain\Replication\ActionHandler\GenerateReplicationListHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Swagger\Annotations as SWG;

class GenerateReplicationListController extends BaseController
{
    /**
     * @var GenerateReplicationListHandler
     */
    private $handler;

    public function __construct(GenerateReplicationListHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @SWG\Response(
     *     response="401",
     *     description="When token has no replication role assigned"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Replication dump in CSV format. '\n\n' separates header from body. Column separator is ';;;'

    ==> Header:
    First header line legend: part[0] = file metadata download template url, part[1] = file content download template url
    .
    .
    ==> Body:
    [1] type (supported: File)
    .
    .
    ==> Body for type 'File':
    [1] type
    [2] file name
    [3] file id (to insert into template link as %file_id%)
    [4] timestamp
    [5] sha256 checksum",
     *     @SWG\Schema(
     *          type="string",
     *          example="http://localhost:8000/replication/file/%file_id/metadata?_token=%token%;;;http://localhost:8000/replication/file/%file_id/content?_token=%token%

    File;;;fe5b5b9567solidarity-with-postal-workers-article-v4;;;2019-12-28 22:37:42;;;272073374953da7ce4c47cc7de17686968295af84add416720af0c99ecc7483a"
     *     )
     * )
     *
     *
     * @param Request $request
     * @param \DateTime|null $since
     *
     * @return Response
     * @throws \Exception
     */
    public function dumpAction(Request $request, \DateTime $since = null): Response
    {
        return $this->wrap(function () use ($request, $since) {
            $csvStream = $this->handler->handle($since, $this->createBaseUrl($request));

            return new StreamedResponse($csvStream, Response::HTTP_OK, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="replication-list.csv"'
            ]);
        });
    }
}
