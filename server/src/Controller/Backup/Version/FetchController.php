<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\FetchHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Version\FetchVersionForm;
use App\Infrastructure\Backup\Form\Version\FetchVersionFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FetchController extends BaseController
{
    private FetchHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(FetchHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     * @param string  $collectionId
     * @param string  $backupId
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $collectionId, string $backupId): Response
    {
        $form = new FetchVersionForm();
        $infraForm = $this->createForm(FetchVersionFormType::class, $form);
        $infraForm->submit([
            'collection' => $collectionId,
            'versionId'  => $backupId,
            'redirect'   => $this->toBoolean($request->get('redirect'), true) !== false,
            'password'   => (string) $request->get('password')
        ]);

        if (!$infraForm->isValid()) {
            return $this->createValidationErrorResponse($infraForm);
        }

        // insert token as input, so the domain can pass it to the redirect
        $form->token = $this->getLoggedUserToken()->getId();

        return $this->wrap(
            function () use ($form) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createVersioningContext($this->getLoggedUserToken())
                );

                if ($response->shouldRedirectToUrl()) {
                    return new RedirectResponse($response->getUrl(), RedirectResponse::HTTP_FOUND);
                }

                return new JsonFormattedResponse($response, $response->getExitCode());
            }
        );
    }
}
