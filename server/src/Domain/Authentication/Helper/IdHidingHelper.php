<?php declare(strict_types=1);

namespace App\Domain\Authentication\Helper;

final class IdHidingHelper
{
    /**
     * Describes how many characters should be hidden in each part of the token
     */
    public const TOKEN_CENSORSHIP_RANGE = [5, 2, 2, 2, 8];

    /**
     * Censors a UUIDv4 string into eg. "*****f40-**87-**7c-**bb-********e8c0"
     *
     * @param string $id
     *
     * @return string
     */
    public static function getStrippedOutToken(string $id): string
    {
        $parts = explode('-', $id);
        $ranges = IdHidingHelper::TOKEN_CENSORSHIP_RANGE;

        foreach ($parts as $num => $value) {
            $parts[$num] = str_replace(
                substr($parts[$num], 0, $ranges[$num]),
                str_repeat('*', $ranges[$num]),
                $parts[$num]
            );
        }

        return implode('-', $parts);
    }

    /**
     * Generates a DQL select string that will censor the UUIDv4
     * eg. "*****f40-**87-**7c-**bb-********e8c0"
     *
     * @param string $columnName
     *
     * @return string
     */
    public static function generateDQLConcatString(string $columnName): string
    {
        $blocksLength = [8, 4, 4, 4, 12];
        $parts = [];

        $subStrPos = 0;

        foreach (self::TOKEN_CENSORSHIP_RANGE as $num => $length) {
            $ending = $num === count(self::TOKEN_CENSORSHIP_RANGE) - 1 ? '' : ',\'-\'';

            $subStrPos += $length + 1;
            $subStrLen = ($blocksLength[$num] - $length);

            $parts[] = '\'' . str_repeat('*', $length) . '\', SUBSTRING(' . $columnName . ', ' . $subStrPos . ', ' . $subStrLen . ')' . $ending;
            $subStrPos += $subStrLen;
        }

        return 'CONCAT(' . implode(',', $parts) . ')';
    }
}
