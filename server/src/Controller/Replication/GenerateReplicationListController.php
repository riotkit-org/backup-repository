<?php declare(strict_types=1);

namespace App\Controller\Replication;

use App\Controller\BaseController;
use App\Domain\Replication\ActionHandler\Server\GenerateReplicationListHandler;
use App\Domain\Replication\Entity\Authentication\Token;
use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Factory\SecurityContextFactory;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class GenerateReplicationListController extends BaseController
{
    private GenerateReplicationListHandler $handler;
    private SecurityContextFactory $contextFactory;

    public function __construct(GenerateReplicationListHandler $handler, SecurityContextFactory $contextFactory)
    {
        $this->handler        = $handler;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @SWG\Parameter(
     *     name="since",
     *     type="string",
     *     format="datetime",
     *     in="query",
     *     required=false,
     *     description="Allows to define a start date. Set empty to get replication list from beginning"
     * )
     *
     * @SWG\Response(
     *     response="403",
     *     description="When token has no replication role assigned"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Replication dump in a JSON stream. Returns multiple JSON documents separated by newline. First line is a header/legend, then two new lines are separating header from body."
     *     )
     * )
     *
     *
     * @param Request $request
     * @return Response
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function dumpAction(Request $request): Response
    {
        $since       = $request->get('since') ? new DateTime($request->get('since')) : null;
        $limit       = (int) $request->get('limit', 256);
        $exampleData = $request->get('example_data') === 'true';

        if ($limit > 2048) {
            throw new \Exception('Cannot increase limit parameter to more than 2048 elements');
        }

        /**
         * @var Token $token
         */
        $token   = $this->getLoggedUserToken(Token::class);
        $context = $this->contextFactory->create($token);

        return new Response(
            $this->handler->handle($since, $context, $limit, $exampleData),
            Response::HTTP_OK,
            [
                'Content-Type'        => 'text/plain',
                'Content-Disposition' => 'attachment; filename="replication-list.jstream"'
            ]
        );
    }
}
