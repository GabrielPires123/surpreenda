<?php

namespace App\Dto\Request;

use Symfony\Component\HttpFoundation\Request;

class EnderecoRequest
{
    public string $logradouro;
    public string $numero;
    public ?string $complemento = null;
    public string $bairro;
    public string $cidade;
    public string $estado;
    public string $cep;

    public static function fromRequest(?Request $request = null): self
    {
        $dto = new self();

        if ($request !== null) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        $requiredFields = ['logradouro', 'numero', 'bairro', 'cidade', 'estado', 'cep'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("O campo '{$field}' é obrigatório.");
            }
        }

        $dto->logradouro = $data['logradouro'];
        $dto->numero = $data['numero'];
        $dto->complemento = $data['complemento'] ?? null;
        $dto->bairro = $data['bairro'];
        $dto->cidade = $data['cidade'];
        $dto->estado = $data['estado'];
        $dto->cep = $data['cep'];

        return $dto;
    }
}
