<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Common\Http;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Context\CachingContext;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Storage\Form\ViewFileFormType;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $form = new ViewFileForm();
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, ViewFileFormType::class);
        $form->filename   = $filename;
        $form->bytesRange = $request->headers->get('Range', '');

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request) {
                $response = $this->handler->handle(
                    $form,
                    $this->createPermissionsContext($form),
                    $this->createCachingContext($request)
                );

                if ($response->getCode() <= Http::HTTP_MAX_OK_CODE) {
                    return new StreamedResponse($response->getResponseCallback(), $response->getCode());
                }

                return new JsonFormattedResponse($response, $response->getCode());
            }
        );
    }

    /**
     * @param Request $request
     *
     * @return CachingContext
     *
     * @throws Exception
     */
    private function createCachingContext(Request $request): CachingContext
    {
        return new CachingContext(
            (string) $request->headers->get('if-none-match'),
            $request->headers->has('if-modified-since') ?
                new \DateTimeImmutable($request->headers->get('if-modified-since')) : null
        );
    }

    private function createPermissionsContext(ViewFileForm $form): ReadSecurityContext
    {
        return $this->authFactory->createViewingContextFromTokenAndForm($this->getLoggedUserToken(), $form);
    }
}
