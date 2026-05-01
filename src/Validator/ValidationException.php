<?php

namespace App\Validator;

/**
 * Exception lançada quando uma validação de entidade falha.
 */
class ValidationException extends \InvalidArgumentException
{
    public function __construct(
        string $message,
        private array $violations = [],
    ) {
        parent::__construct($message);
    }

    public function getViolations(): array
    {
        return $this->violations;
    }

    public function getFirstViolation(): string
    {
        return $this->violations[0] ?? $this->getMessage();
    }
}
