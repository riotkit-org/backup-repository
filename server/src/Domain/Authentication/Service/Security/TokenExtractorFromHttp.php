<?php declare(strict_types=1);

namespace App\Domain\Authentication\Service\Security;

/**
 * Extracts authorization token from HTTP headers
 */
class TokenExtractorFromHttp
{
    public static function extractFromHeaders(array $headers): ?string
    {
        foreach ($headers as $header => $values) {
            foreach ($values as $value) {
                $normalized = strtolower(trim($header));
                $value = trim($value);

                if ($normalized === 'authorization' && strpos(strtolower($value), 'bearer ') === 0) {
                    return substr($value, strlen('bearer '));
                }
            }
        }

        return null;
    }
}
