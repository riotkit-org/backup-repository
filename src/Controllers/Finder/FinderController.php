<?php declare(strict_types=1);

namespace Controllers\Finder;

use Actions\Finder\FindAction;
use Controllers\AbstractBaseController;
use Model\Request\SearchQueryPayload;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @package Controllers\Finder
 */
class FinderController extends AbstractBaseController
{
    /**
     * @return string
     */
    protected function getPayloadClassName(): string
    {
        return SearchQueryPayload::class;
    }

    /**
     * @return SearchQueryPayload
     */
    protected function getPayload()
    {
        return parent::getPayload();
    }

    /**
     * @return JsonResponse
     */
    public function findAction()
    {
        $action = new FindAction(
            $this->getContainer()->offsetGet('repository.file'),
            $this->getContainer()->offsetGet('manager.storage')
        );

        $action->setPayload($this->getPayload());

        return new JsonResponse($action->execute());
    }
}
