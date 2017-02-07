<?php declare(strict_types=1);

namespace Controllers\Auth;

use Commands\ClearExpiredTokensCommand;
use Controllers\AbstractBaseController;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Clearing expired tokens
 * =======================
 *
 * @package Controllers\Auth
 */
class ExpiredTokensController extends AbstractBaseController
{
    /**
     * @return JsonResponse
     */
    public function clearExpiredTokensAction()
    {
        $command = new ClearExpiredTokensCommand();
        $command->setApp($this->getContainer());
        $command->executeCommand(new StringInput(''), new NullOutput());

        return new JsonResponse(['success' => true, 'processed' => $command->getProcessedAmount()]);
    }
}
