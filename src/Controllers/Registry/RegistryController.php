<?php

namespace Controllers\Registry;

use Controllers\AbstractBaseController;

class RegistryController extends AbstractBaseController
{
    public function checkExistsAction()
    {

    }

    public function deleteAction()
    {
        $imageName = $this->getRequest()->request->get('file_name');


    }
}