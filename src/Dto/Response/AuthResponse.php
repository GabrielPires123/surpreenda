<?php

namespace App\Dto\Response;

class AuthResponse
{
    public string $token;
    public ClienteResponse $cliente;

    public function __construct(string $token, ClienteResponse $cliente)
    {
        $this->token = $token;
        $this->cliente = $cliente;
    }
}
