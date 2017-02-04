<?php

namespace Controllers;

use Actions\AbstractBaseAction;
use Manager\Domain\TokenManagerInterface;
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
     * @var bool $isInternalRequest
     */
    private $isInternalRequest = false;

    /**
     * @param Application $app
     * @param bool        $isInternalRequest
     */
    public function __construct(Application $app, bool $isInternalRequest = false)
    {
        $this->container  = $app;
        $this->request    = $app['request_stack']->getCurrentRequest();
        $this->setIsInternalRequest($isInternalRequest);

        $this->assertValidateAccessRights($this->request, $app['manager.token'], $this->getRequiredRoleName());
    }

    /**
     * @return string
     */
    public function getRequiredRoleName()
    {
        return '';
    }

    /**
     * @return \Twig_Environment
     */
    public function getRenderer(): \Twig_Environment
    {
        return $this->getContainer()->offsetGet('twig');
    }

    /**
     * @param Request               $request
     * @param TokenManagerInterface $tokenManager
     * @param string                $roleName
     */
    public function assertValidateAccessRights(
        Request $request,
        TokenManagerInterface $tokenManager,
        string $roleName = ''
    ) {
        if ($this->isInternalRequest === true) {
            return;
        }

        $inputToken = $request->get('_token') ?? '';

        if (!$tokenManager->isTokenValid($inputToken, $roleName)) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Access denied, please verify the "_token" parameter');
        }
    }

    /**
     * @param bool $isInternalRequest
     * @return $this
     */
    public function setIsInternalRequest(bool $isInternalRequest)
    {
        $this->isInternalRequest = $isInternalRequest;
        return $this;
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
     * @param Request $request
     * @return AbstractBaseController
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
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