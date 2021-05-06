<?php declare(strict_types=1);

namespace Tests\Domain\Technical\Service;

use App\Domain\Technical\Service\InfluxDBMetricsFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @see InfluxDBMetricsFormatter
 */
class InfluxDBMetricsFormatterTest extends TestCase
{
    public function testFormatsIntegersAndStringsOnly(): void
    {
        $formatter = new InfluxDBMetricsFormatter();
        $toFormat = [
            'data' => [
                'books' => [
                    'bakunin'   => 1,
                    'kropotkin' => '5',
                    'malatesta' => 'yes',
                    'boolean'   => true,  // should not appear
                    'array'     => [true] // should not appear
                ]
            ]
        ];

        $formatted = $formatter->format($toFormat, 'http://localhost', 'test');

        $this->assertStringContainsString('bakunin=1', $formatted);
        $this->assertStringContainsString('kropotkin=5', $formatted);
        $this->assertStringContainsString('malatesta="yes"', $formatted);

        $this->assertStringNotContainsString('boolean=', $formatted);
        $this->assertStringNotContainsString('array=', $formatted);
    }

    public function testAppEnvAndBaseUrlArePresentAsTags(): void
    {
        $formatter = new InfluxDBMetricsFormatter();
        $formatted = $formatter->format(['data' => []], 'http://localhost', 'test');

        $this->assertStringContainsString('backup_repository_report,base_url="http://localhost",app_env="test"  ', $formatted);
    }

    public function testTimestampIsPresentAtTheEndOfMetricString(): void
    {
        $formatter = new InfluxDBMetricsFormatter();
        $formatted = $formatter->format(['data' => []], 'http://localhost', 'test');

        $exp = explode(' ', $formatted);

        $this->assertIsNumeric(str_replace('us', '', $exp[count($exp) - 1]));
    }
}
