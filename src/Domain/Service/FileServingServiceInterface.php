<?php declare(strict_types=1);

namespace Domain\Service;

/**
 * Outputs the file to the web browser
 * -----------------------------------
 *   - Verifies browser cache
 *   - Builds headers
 *   - Streaming to browser
 *
 * @package Service
 */
interface FileServingServiceInterface
{
    /**
     * Create a closure that will output self to the browser
     *
     * @param string $filePath
     * @return \Closure
     */
    public function buildClosure(string $filePath): \Closure;

    /**
     * Basing on the headers passed from browser decide if we are going
     * to stream a new file, or just return ETag information
     *
     * @param string $filePath
     * @param $modifiedSince
     * @param $noneMatch
     * @return bool
     */
    public function shouldServe(string $filePath, $modifiedSince, $noneMatch): bool;

    /**
     * Output headers to serve
     *
     * @param string $filePath
     * @return array
     */
    public function buildOutputHeaders(string $filePath): array;
}
