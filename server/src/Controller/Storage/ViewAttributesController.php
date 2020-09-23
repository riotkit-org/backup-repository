<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\ViewAttributesHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\ViewFileForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Response;

class ViewAttributesController extends BaseController
{
    private ViewAttributesHandler  $handler;
    private SecurityContextFactory $ctxFactory;

    public function __construct(ViewAttributesHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler    = $handler;
        $this->ctxFactory = $authFactory;
    }

    /**
     * @todo SWAGGER DOCS
     *
     * @param string $filename
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function viewAttributesAction(string $filename): Response
    {
        $form = new ViewFileForm();
        $form->filename = $filename;

        return $this->wrap(
            function () use ($filename, $form) {
                $response = $this->handler->handle(
                    $filename,
                    $this->ctxFactory->createViewingContextFromTokenAndForm($this->getLoggedUserToken(), $form, false)
                );

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }
}
