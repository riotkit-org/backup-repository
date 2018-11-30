<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\ValueObject\HttpMethod;
use App\Domain\Backup\ValueObject\Url;
use App\Domain\Backup\ValueObject\WebhookBody;
use App\Domain\Backup\ValueObject\WebhookEventList;

class Webhook
{
    /**
     * Possible values: upload_success, upload_failure
     *
     * @var WebhookEventList
     */
    private $events;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var HttpMethod
     */
    private $method;

    /**
     * Body could contain a place to insert error code and error message eg. {{ error_code }}, {{ error_message }}
     *
     * @var WebhookBody
     */
    private $body;
}
