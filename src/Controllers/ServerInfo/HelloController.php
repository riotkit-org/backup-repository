<?php

namespace Controllers\ServerInfo;

use Controllers\AbstractBaseController;
use Service\Versioning;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Says hello with a code
 *
 * @package Controllers\Serverinfo
 */
class HelloController extends AbstractBaseController
{
    /** @var Versioning $versioning */
    protected $versioning;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->versioning = $app['versioning'];
    }

    /**
     * @return JsonResponse
     */
    public function viewAction()
    {
        return new JsonResponse([
            'code'    => 200,
            'message' => 'Hello, welcome. Please take a look at /repository/routing/map for the list of available routes.',
            'version' => [
                'version' => $this->getVersioning()->getVersion(),
                'release' => $this->getVersioning()->getReleaseNumber(),
            ],
        ], 200);
    }

    /**
     * @return Versioning
     */
    public function getVersioning()
    {
        return $this->versioning;
    }
}