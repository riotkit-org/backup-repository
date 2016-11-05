<?php

namespace Actions;

use Controllers\AbstractBaseController;
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
     * @override
     */
    protected function constructServices()
    {

    }

    /**
     * @param Application $app
     * @return $this
     */
    public function setContainer(Application $app)
    {
        $this->container = $app;
        $this->constructServices();
        return $this;
    }

    /**
     * @param AbstractBaseController $controller
     * @return $this
     */
    public function setController(AbstractBaseController $controller)
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