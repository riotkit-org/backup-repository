<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Infrastructure\Common\Service\ORMConnectionCheck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Swagger\Annotations as SWG;

/**
 * Returns status information
 */
class HealthController extends AbstractController
{
    /**
     * @var FilesystemManager
     */
    private $fs;

    /**
     * @var ORMConnectionCheck
     */
    private $ormConnectionCheck;

    /**
     * @var string
     */
    private $secretCode;

    public function __construct(FilesystemManager $fs, ORMConnectionCheck $ORMConnectionCheck, string $secretCode)
    {
        $this->fs                 = $fs;
        $this->ormConnectionCheck = $ORMConnectionCheck;
        $this->secretCode         = $secretCode;
    }

    /**
     * @SWG\Response(
     *     response="400",
     *     description="When the 'code' parameter does not match the 'HEALTH_CHECK_CODE' setting"
     * )
     *
     * @SWG\Response(
     *     response="503",
     *     description="Same response format as for 200 code. 503 is when at least one check from list failed"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Health report",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="status",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(property="storage", type="boolean"),
     *                  @SWG\Property(property="database", type="boolean")
     *              )
     *          ),
     *          @SWG\Property(
     *              property="messages",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(property="storage", type="array",
     *                      @SWG\Items(type="string")
     *                  ),
     *                  @SWG\Property(property="database", type="array",
     *                      @SWG\Items(type="string")
     *                  )
     *              )
     *          ),
     *          @SWG\Property(property="global_status", type="boolean"),
     *          @SWG\Property(
     *              property="ident",
     *              type="array",
     *              @SWG\Items(
     *                  type="string"
     *              )
     *          )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function healthAction(Request $request): JsonResponse
    {
        if ($request->get('code') !== $this->secretCode || !$request->get('code')) {
            throw new AccessDeniedHttpException();
        }

        $storageIsOk = true;
        $dbIsOk      = true;
        $messages = ['database' => [], 'storage' => []];

        try {
            $dbIsOk = $this->ormConnectionCheck->test();

        } catch (\Exception $exception) {
            $messages['database'][] = $exception->getMessage();
        }

        try {
            $this->fs->test();

        } catch (StorageException $exception) {
            $storageIsOk = false;
            $messages['storage'][] = $exception->getMessage();

            if ($exception->getPrevious()) {
                $messages['storage'][] = $exception->getPrevious()->getMessage();
            }
        }

        $globalStatus = $storageIsOk && $dbIsOk;

        return new JsonResponse(
            json_encode(
                [
                    'status' => [
                        'storage'  => $storageIsOk,
                        'database' => $dbIsOk
                    ],
                    'messages'      => $messages,
                    'global_status' => $globalStatus,
                    'ident'         => [
                        'global_status=' . $this->boolToStr($globalStatus),
                        'storage=' . $this->boolToStr($storageIsOk),
                        'database=' . $this->boolToStr($dbIsOk)
                    ]
                ],
                JSON_PRETTY_PRINT
            ),
            $globalStatus ? JsonResponse::HTTP_OK : JsonResponse::HTTP_SERVICE_UNAVAILABLE,
            [],
            true
        );
    }

    private function boolToStr(bool $value): string
    {
        return $value ? 'True' : 'False';
    }
}
