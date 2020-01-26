<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Service;

use App\Domain\Replication\Service\JSONSchemaValidationService;
use JsonSchema\Validator;
use Psr\Log\LoggerInterface;

class JustinRainbowJsonSchemaValidationService implements JSONSchemaValidationService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function validateAndGetErrorsAsText(array $data, array $schema): string
    {
        $dataAsObject = Validator::arrayToObjectRecursive($data);

        $validator = new Validator();
        $validator->validate($dataAsObject, $schema);

        if ($validator->isValid()) {
            $this->logger->debug('Data is matching the schema!');
            return '';
        }

        $summary = '';

        foreach ($validator->getErrors() as $error) {
            $this->logger->error('Field "' . $error['property'] . '" does not match the schema');

            $summary .= 'field=' . $error['property'] . ', msg=' . $error['message'] . "\n";
        }

        return $summary;
    }
}
