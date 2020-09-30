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
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Download selected version of file in the collection
     *
     * @SWG\Parameter(
     *     type="string",
     *     in="path",
     *     name="collectionId",
     *     description="Id of a collection"
     * )
     *
     * @SWG\Parameter(
     *     type="string",
     *     in="path",
     *     name="backupId",
     *     description="Id of a backup, or a version name eg. v1, v2, latest"
     * )
     *
     * @SWG\Parameter(
     *     type="boolean",
     *     in="query",
     *     name="redirect",
     *     description="Should immediately redirect to the backup download URL that points at storage?"
     * )
     *
     * @SWG\Parameter(
     *     type="string",
     *     in="query",
     *     name="password",
     *     description="If the file is password protected, then a password needs to be entered there"
     * )
     *
     * @SWG\Response(
     *     response="302",
     *     description="Returns a HTTP redirection to storage if ?redirect=true"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns JSON with url to the file download",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="status",
     *             type="boolean",
     *             example=true
     *         ),
     *         @SWG\Property(
     *             property="http_code",
     *             type="integer",
     *             example="200"
     *         ),
     *         @SWG\Property(
     *             property="exit_code",
     *             type="integer",
     *             example="0"
     *         ),
     *         @SWG\Property(
     *             property="url",
     *             type="string",
     *             example="https://api.storage.iwa-ait.org/repository/file/class-struggle.pdf"
     *         )
     *     )
     * )
     *
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
        $form->token = $this->getLoggedUser()->getId();

        return $this->wrap(
            function () use ($form) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createVersioningContext($this->getLoggedUser())
                );

                if ($response->isSuccess()) {
                    return new StreamedResponse($response->getCallback(), $response->getExitCode());
                }

                return new JsonFormattedResponse($response, $response->getExitCode());
            }
        );
    }
}
