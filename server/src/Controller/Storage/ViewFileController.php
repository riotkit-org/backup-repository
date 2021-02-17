<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Response\FileDownloadResponse;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
