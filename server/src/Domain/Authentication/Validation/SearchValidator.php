<?php declare(strict_types=1);

namespace App\Domain\Authentication\Validation;

use App\Domain\Authentication\Exception\SearchFormException;
use App\Domain\Common\Exception\DomainAssertionFailure;

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
        $issues = [];

        // we cannot allow any user to create too much RAM consuming queries
        if ($limit > 1000) {
            $issues[] = SearchFormException::fromQueryLimitTooHighCause();
        }

        if ($limit < 1) {
            $issues[] = SearchFormException::fromQueryLimitCannotBeNegativeCause();
        }

        if ($page <= 0) {
            $issues[] = SearchFormException::fromPageCannotBeNegativeCause();
        }

        if ($issues) {
            throw DomainAssertionFailure::fromErrors($issues);
        }
    }
}
