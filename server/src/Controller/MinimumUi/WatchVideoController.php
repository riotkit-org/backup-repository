<?php declare(strict_types=1);

namespace App\Controller\MinimumUi;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WatchVideoController extends BaseController
{
    public function handle(Request $request, string $fileId): Response
    {
        return $this->render('minimumui/WatchVideo.html.twig', [
            'fileUrl' => $this->getFileDownloadUrl($fileId, $request),
            'title'   => $fileId,
            'locale'  => $request->getLocale()
        ]);
    }

    private function getFileDownloadUrl(string $fileId, Request $request): string
    {
        $query = $request->query->all();
        unset($query['_token']);

        return $this->generateUrl('storage.get_file',
            array_merge(
                $query,
                [
                    'filename' => $fileId
                ]
            )
        );
    }
}
