<?php declare(strict_types=1);

namespace App\Domain\Technical\Service;

class InfluxDBMetricsFormatter
{
    public function format(array $toFormat, string $baseUrl, string $appEnv): string
    {
        $formattedAsInflux = 'backup_repository_report,base_url="' . $baseUrl . '",app_env="' . $appEnv . '"';
        $metricsToGlue = [];

        foreach ($toFormat['data'] as $metricGroupName => $metrics) {
            foreach ($metrics as $metricName => $value) {
                // catch only integer and string type metrics, ignore array metrics
                if (!is_string($value) && !is_integer($value)) {
                    continue;
                }

                if (is_string($value) && !is_numeric($value)) {
                    $value = '"' . $value . '"';
                }

                $metricsToGlue[] = str_replace("\n", '', $metricGroupName . '_' . $metricName . '=' . $value);
            }
        }

        return $formattedAsInflux . ' ' . implode(',', $metricsToGlue) . ' ' . $this->getCurrentTimestamp() . 'us';
    }

    private function getCurrentTimestamp(): string
    {
        list($uSec, $sec) = explode(' ', microtime());

        return sprintf('%d%06d', $sec, $uSec*1000000);
    }
}
