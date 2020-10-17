<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Response\FileDownloadResponse;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Storage\Form\ViewFileFormType;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Swagger\Annotations as SWG;

class ViewFileController extends BaseController
{
    private ViewFileHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(ViewFileHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Download a file
     *
     * @SWG\Parameter(
     *     name="filename",
     *     in="path",
     *     type="string",
     *     description="Filename"
     * )
     *
     * @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     type="string",
     *     required=false,
     *     description="Optionally a password if file is password protected"
     * )
     *
     * @SWG\Parameter(
     *     name="Range",
     *     type="string",
     *     in="header",
     *     description="HTTP Byte-Range support"
     * )
     *
     * @SWG\Parameter(
     *     name="If-None-Match",
     *     type="string",
     *     in="header",
     *     description="HTTP caching header"
     * )
     *
     * @SWG\Parameter(
     *     name="If-modified-since",
     *     type="string",
     *     in="header",
     *     description="HTTP caching header"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns file contents"
     * )
     *
     * @param Request $request
     * @param string $filename
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handle(Request $request, string $filename): Response
    {
        /**
         * @var ViewFileForm $form
         */
        $form = $this->decodeRequestIntoDTO($request->query->all(), ViewFileForm::class);

        $form->filename   = $filename;
        $form->bytesRange = $request->headers->get('Range', '');

        $response = $this->handler->handle(
            $form,
            $this->createPermissionsContext($form),
        );

        //
        // Flush a file: headers + body
        // In headers we expect bytes range, caching etc.
        //
        if ($response instanceof FileDownloadResponse && $response->isFlushingFile()) {
            return new StreamedResponse(
                static function () use ($response) {
                    $input = $response->getResponseStream()->detach();
                    $output = fopen('php://output', 'wb');

                    // headers first
                    $headers = $response->getHeaders();

                    foreach ($headers as $header => $value) {
                        @header($header . ': ' . $value);
                    }

                    // flush the content, including the HTTP-like behavior (bytes range support etc.)
                    $contentFlush = $response->getContentFlushCallback();
                    $contentFlush($input, $output);

                    @fclose($input);
                    @fclose($output);
                },
                $response->getCode()
            );
        }

        return new JsonFormattedResponse($response, $response->getCode());
    }

    private function createPermissionsContext(ViewFileForm $form): ReadSecurityContext
    {
        return $this->authFactory->createViewingContextFromTokenAndForm($this->getLoggedUserOrAnonymousToken(), $form);
    }
}
