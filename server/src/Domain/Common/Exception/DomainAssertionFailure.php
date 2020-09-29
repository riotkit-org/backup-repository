<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

class DomainAssertionFailure extends ApplicationException
{
    /**
     * @var DomainInputValidationConstraintViolatedError[]
     */
    protected array $constraintsViolated = [];

    /**
     * @param array $violations
     * @param string $message
     * @param int $code
     *
     * @return DomainAssertionFailure|static
     */
    public static function fromErrors(array $violations, string $message = 'Domain validation not passed',
                                      int $code = 0): DomainAssertionFailure
    {
        $new = new static($message, $code);
        $new->constraintsViolated = $violations;

        return $new;
    }

    /**
     * @return DomainInputValidationConstraintViolatedError[]
     */
    public function getConstraintsViolated(): array
    {
        return $this->constraintsViolated;
    }
}
