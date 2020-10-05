<?php declare(strict_types=1);

namespace App\Domain\Common\Exception;

class DomainAssertionFailure extends ApplicationException
{
    /**
     * @var DomainInputValidationConstraintViolatedError[]
     */
    protected array $constraintsViolated = [];

    /**
     * @param DomainInputValidationConstraintViolatedError[] $violations
     * @param string $message
     * @param int $code
     *
     * @return DomainAssertionFailure|static
     */
    public static function fromErrors(array $violations, string $message = '',
                                      int $code = 0): DomainAssertionFailure
    {
        if (!$message) {
            $message = 'Domain validation failure; ';

            foreach ($violations as $violation) {
                $message .= $violation->getMessage() . "\n";
            }
        }

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
