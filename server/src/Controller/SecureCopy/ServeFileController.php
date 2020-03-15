<?php declare(strict_types=1);

namespace App\Controller\SecureCopy;

use App\Controller\BaseController;
use App\Domain\SecureCopy\ActionHandler\ServeFileContentHandler;
use App\Domain\SecureCopy\Entity\Authentication\Token;
use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Exception\CryptoMapNotFoundError;
use App\Domain\SecureCopy\Factory\SecurityContextFactory;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Swagger\Annotations as SWG;
use function GuzzleHttp\Psr7\copy_to_stream;

class ServeFileController extends BaseController
{
    private ServeFileContentHandler $handler;
    private SecurityContextFactory $contextFactory;

    public function __construct(ServeFileContentHandler $handler, SecurityContextFactory $contextFactory)
    {
        $this->handler        = $handler;
        $this->contextFactory = $contextFactory;
    }

    /**
     * Serve a file content through SecureCopy protocol
     *
     * @SWG\Response(
     *     response="200",
     *     description="Binary file content.
     * - Raw file content, when the CURRENT TOKEN does not have set encryption.
     * - Encrypted content, when the CURRENT TOKEN have set encryption for zero-knowledge securecopy (securecopy client is not aware of replicated file contents)"
     * )
     *
     * @SWG\Response(
     *     response="403",
     *     description="When token has no securecopy role assigned"
     * )
     *
     * @param string $fileName
     *
     * @return Response
     * @throws AuthenticationException
     * @throws CryptoMapNotFoundError
     */
    public function fetchAction(string $fileName): Response
    {
        $output = new Stream(fopen('php://output', 'wb'));

        /**
         * @var Token $token
         */
        $token    = $this->getLoggedUserToken(Token::class);
        $context  = $this->contextFactory->create($token);

        // act, and get response
        $response = $this->handler->handle($fileName, $context);

        return $this->wrap(function () use ($response, $output) {
            return new StreamedResponse(
                static function () use ($response, $output) {
                    copy_to_stream($response->getStream(), $output);
                },
                $response->getStatusCode(),
                $response->getHeaders()
            );
        });
    }
}
