<?php

namespace Controllers;

use Actions\AbstractBaseAction;
use Silex\Application;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package Controllers
 */
abstract class AbstractBaseController
{
    /**
     * @var Application $container
     */
    private $container;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->container  = $app;
        $this->request    = $app['request_stack']->getCurrentRequest();

        $this->assertValidateAccessRights($this->request, $app['api.key']);
    }

    /**
     * @param Request $request
     * @param string $allowedToken
     */
    public function assertValidateAccessRights(Request $request, $allowedToken)
    {
        if ($request->get('_token') != $allowedToken) {
            throw new AccessDeniedException('Ouh, sorry, the "_token" field does not contain a valid value');
        }
    }

    /**
     * @return Application
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param AbstractBaseAction $action
     * @return AbstractBaseAction
     */
    protected function getAction(AbstractBaseAction $action)
    {
        $action->setContainer($this->container);
        $action->setController($this);

        return $action;
    }
}