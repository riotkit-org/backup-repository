<?php

namespace Controllers\ServerInfo;

use Controllers\AbstractBaseController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @package Controllers\Serverinfo
 */
class StatsController extends AbstractBaseController
{
    /**
     * @return JsonResponse
     */
    public function viewAction()
    {
        return new JsonResponse([
            'code'    => 200,
            'message' => 'Currently nothing to show, yet.',
        ], 200);
    }
}