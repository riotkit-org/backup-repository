<?php

namespace Controllers\ServerInfo;

use Actions\ServerInfo\StatsProviderAction;
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
        $action = new StatsProviderAction();
        $action->setContainer($this->getContainer());
        $action->setController($this);

        return new JsonResponse([
            'success' => true,
            'data'    => $action->execute(),
        ], 200);
    }
}