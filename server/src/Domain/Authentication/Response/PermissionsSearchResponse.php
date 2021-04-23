<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Common\Response\NormalSearchResponse;

class PermissionsSearchResponse extends NormalSearchResponse
{
    protected int $page      = 1;
    protected int $pageLimit = 1000;
    protected int $maxPages  = 1;

    private array $allPermissions;

    public static function createResultsResponse(array $scopedPermissions, array $allPermissions): PermissionsSearchResponse
    {
        $response = new PermissionsSearchResponse();
        $response->status         = true;
        $response->data           = $scopedPermissions;
        $response->allPermissions = $allPermissions;

        return $response;
    }

    public function jsonSerialize(): array
    {
        return [
            'message'         => $this->message,
            'status'          => $this->status,
            'permissions'     => $this->data,
            'all_permissions' => $this->allPermissions
        ];
    }
}
