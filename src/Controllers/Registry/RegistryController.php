<?php

namespace Controllers\Registry;

use Actions\AbstractBaseAction;
use Actions\Registry\CheckExistAction;
use Actions\Registry\DeleteAction;

use Controllers\AbstractBaseController;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @package Controllers\Registry
 */
class RegistryController extends AbstractBaseController
{
    const RESPONSE_CODE_NOT_FOUND = 'file_not_found';

    /**
     * @return JsonResponse
     */
    public function checkExistsAction()
    {
        $act = $this->getAction(new CheckExistAction(
            $this->getRequest()->request->get('file_name')
        ));

        return $this->getActionResponse($act);
    }

    /**
     * @return JsonResponse
     */
    public function deleteAction()
    {
        $act = $this->getAction(new DeleteAction(
            $this->getRequest()->request->get('file_name')
        ));

        return $this->getActionResponse($act);
    }

    /**
     * Handles a common exception
     * and returns a response in common format
     *
     * @param AbstractBaseAction $act
     * @return JsonResponse
     */
    private function getActionResponse($act)
    {
        $actionName = explode('\\', get_class($act));
        $actionName = end($actionName);

        try {
            return new JsonResponse([
                'success' => true,
                'action'  => $actionName,
                'data'    => $act->execute(),
            ]);

        } catch (FileNotFoundException $e) {
            return new JsonResponse([
                'success' => false,
                'action'  => $actionName,
                'code'    => self::RESPONSE_CODE_NOT_FOUND,
                'message' => 'Requested file does not exists',
            ]);
        }
    }
}