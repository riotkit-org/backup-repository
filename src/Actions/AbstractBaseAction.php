<?php

namespace Actions;

use Controllers\Upload\UploadControllerInterface;
use Silex\Application;

/**
 * @package Controllers
 */
abstract class AbstractBaseAction
{
    /**
     * @var Application $container
     */
    private $container;

    /**
     * @var UploadControllerInterface $controller
     */
    private $controller;

    /**
     * @return array
     */
    abstract public function execute(): array;

    /**
     * @param Application $app
     * @return $this
     */
    public function setContainer(Application $app)
    {
        $this->container = $app;
        return $this;
    }

    /**
     * @param UploadControllerInterface $controller
     * @return $this
     */
    public function setController(UploadControllerInterface $controller)
    {
        $this->controller = $controller;
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
     * @return UploadControllerInterface
     */
    public function getController()
    {
        return $this->controller;
    }
}