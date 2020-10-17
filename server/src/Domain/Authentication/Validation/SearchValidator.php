<?php declare(strict_types=1);

namespace App\Domain\Authentication\Validation;

use App\Domain\Authentication\Exception\SearchFormException;

class SearchValidator
{
    /**
     * @param int $page
     * @param int $limit
     *
     * @throws SearchFormException
     */
    public function validateSearchCanBePerformed(int $page, int $limit): void
    {
        // we cannot allow any user to create too much RAM consuming queries
        if ($limit > 1000) {
            throw SearchFormException::fromQueryLimitTooHighCause();
        }

        if ($limit < 1) {
            throw SearchFormException::fromQueryLimitCannotBeNegativeCause();
        }

        if ($page <= 0) {
            throw SearchFormException::fromPageCannotBeNegativeCause();
        }
    }
}