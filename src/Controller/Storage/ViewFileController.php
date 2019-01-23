<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\ViewFileHandler;
use App\Domain\Storage\Context\CachingContext;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Infrastructure\Storage\Form\ViewFileFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewFileController extends BaseController
{
    /**
     * @var ViewFileHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(ViewFileHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     * @param string $filename
     *
     * @return Response
     *
     * @throws \Exception
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

                if ($response->getCode() === Response::HTTP_OK) {
                    return new StreamedResponse($response->getResponseCallback());
                }

                return new JsonResponse($response, $response->getCode());
            }
        );
    }

    /**
     * @param Request $request
     *
     * @return CachingContext
     *
     * @throws \Exception
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
