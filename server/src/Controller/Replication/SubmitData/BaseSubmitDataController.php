<?php declare(strict_types=1);

namespace App\Controller\Replication\SubmitData;

use App\Controller\BaseController;
use App\Domain\Common\Exception\BusException;
use App\Domain\Replication\ActionHandler\ServeSubmitDataHandler;
use App\Domain\Replication\Entity\Authentication\Token;
use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Factory\SecurityContextFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

abstract class BaseSubmitDataController extends BaseController
{
    /**
     * @var ServeSubmitDataHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $contextFactory;

    public function __construct(ServeSubmitDataHandler $handler, SecurityContextFactory $contextFactory)
    {
        $this->handler        = $handler;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param Request $request
     * @param string $fileName
     *
     * @return Response
     *
     * @throws BusException
     * @throws AuthenticationException
     */
    public function dumpSubmitDataAction(Request $request, string $fileName): Response
    {
        /**
         * @var Token $token
         */
        $token    = $this->getLoggedUserToken(Token::class);
        $response = $this->handler->handle($this->getDataType(), $fileName, $this->contextFactory->create($token));

        if (\strtolower((string) $request->get('raw')) === 'true') {
            return new JsonResponse($response->getObject(), $response->getStatusCode());
        }

        return new JsonResponse($response, $response->getStatusCode());
    }

    abstract protected function getDataType(): string;
}
