<?php declare(strict_types=1);

namespace App\Controller\SecureCopy\SubmitData;

use App\Domain\Common\Exception\BusException;
use App\Domain\SubmitDataTypes;
use App\Domain\SecureCopy\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class ServeSubmitDataController extends BaseSubmitDataController
{
    /**
     * Serve specific file metadata for SecureCopy operation
     *
     * @SWG\Parameter(
     *     name="fileName",
     *     type="string",
     *     in="path",
     *     required=true,
     *     description="File name eg. 5d080a2cecpoczta-tv-reportaz.mp4"
     * )
     *
     * @SWG\Parameter(
     *     name="raw",
     *     type="boolean",
     *     in="query",
     *     required=true,
     *     description="Return as raw object, or with response status information"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Form data required for resubmission of given file on other File Repostory node. When ?raw=true, then only 'object' part is returned as root level element",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="status",
     *              type="string",
     *              example="OK"
     *          ),
     *          @SWG\Property(
     *              property="http_code",
     *              type="integer",
     *              example="200"
     *          ),
     *          @SWG\Property(
     *              property="object",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  example="14"
     *              ),
     *              @SWG\Property(
     *                  property="fileName",
     *                  type="string",
     *                  example="5d080a2cecpoczta-tv-reportaz.mp4"
     *              ),
     *              @SWG\Property(
     *                  property="contentHash",
     *                  type="string",
     *                  example="197b9e3b089c4dd445ed777449c7d3abf46ec363f1a930d19f3ee306e73a7405",
     *                  description="Sha256 sum"
     *              ),
     *              @SWG\Property(
     *                  property="dateAdded",
     *                  type="string",
     *                  format="datetime",
     *                  description="Date and time in format 'Y-m-d H:i:s'",
     *                  example="2020-01-05 16:27:22"
     *              ),
     *              @SWG\Property(
     *                  property="mimeType",
     *                  type="string",
     *                  example="video/mp4"
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string",
     *                  example=""
     *              ),
     *              @SWG\Property(
     *                  property="public",
     *                  type="boolean",
     *                  example="true"
     *              )
     *          )
     *     )
     * )
     *
     * @param Request $request
     *
     * @param string $fileName
     *
     * @return Response
     *
     * @throws BusException
     * @throws AuthenticationException
     * @throws \App\Domain\SecureCopy\Exception\ValidationException
     */
    public function dumpSubmitDataAction(Request $request, string $fileName): Response
    {
        return parent::dumpSubmitDataAction($request, $fileName);
    }

    protected function getDataType(): string
    {
        return SubmitDataTypes::TYPE_FILE;
    }
}
