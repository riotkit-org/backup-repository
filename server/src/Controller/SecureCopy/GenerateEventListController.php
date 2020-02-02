<?php declare(strict_types=1);

namespace App\Controller\SecureCopy;

use App\Controller\BaseController;
use App\Domain\SecureCopy\ActionHandler\GenerateEventListHandler;
use App\Domain\SecureCopy\Entity\Authentication\Token;
use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Exception\ValidationException;
use App\Domain\SecureCopy\Factory\SecurityContextFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use DateTime;

class GenerateEventListController extends BaseController
{
    private GenerateEventListHandler $handler;
    private SecurityContextFactory $contextFactory;

    public function __construct(GenerateEventListHandler $handler, SecurityContextFactory $contextFactory)
    {
        $this->handler        = $handler;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @SWG\Parameter(
     *     name="type",
     *     type="string",
     *     in="path",
     *     required=true,
     *     allowEmptyValue=false,
     *     description="Object type. Supported types: file"
     * )
     *
     * @SWG\Parameter(
     *     name="since",
     *     type="string",
     *     format="datetime",
     *     in="query",
     *     required=false,
     *     description="Allows to define a start date. Set empty to get securecopy list from beginning"
     * )
     *
     * @SWG\Response(
     *     response="403",
     *     description="When token has no securecopy role assigned"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="SecureCopy dump in a JSON stream. Returns multiple JSON documents separated by newline. First line is a header/legend, then two new lines are separating header from body."
     *     )
     * )
     *
     * @param Request $request
     * @param string  $type
     *
     * @return Response
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function dumpAction(Request $request, string $type): Response
    {
        $since       = $request->get('since') ? new DateTime($request->get('since')) : null;
        $limit       = (int) $request->get('limit', 256);

        /**
         * @var Token $token
         */
        $token   = $this->getLoggedUserToken(Token::class);
        $context = $this->contextFactory->create($token);

        return new Response(
            $this->handler->handle($since, $context, $limit, $type),
            Response::HTTP_OK,
            [
                'Content-Type'        => 'text/plain',
                'Content-Disposition' => 'attachment; filename="securecopy-list.jstream"'
            ]
        );
    }
}
