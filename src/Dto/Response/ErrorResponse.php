<?php

namespace App\Dto\Response;

class ErrorResponse
{
    public string $error;

    public function __construct(string $error)
    {
        $this->error = $error;
    }
}
