<?php

namespace App\Dto\Response;

class EnderecoResponse
{
    public string $id;
    public string $cep;
    public string $logradouro;
    public string $numero;
    public ?string $complemento = null;
    public string $bairro;
    public string $cidade;
    public string $estado;
    public bool $isPrincipal;

    public function __construct(
        string $id,
        string $cep,
        string $logradouro,
        string $numero,
        ?string $complemento,
        string $bairro,
        string $cidade,
        string $estado,
        bool $isPrincipal
    ) {
        $this->id = $id;
        $this->cep = $cep;
        $this->logradouro = $logradouro;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->isPrincipal = $isPrincipal;
    }

    public static function fromEntity(\App\Entity\Endereco $entity): self
    {
        return new self(
            $entity->getId(),
            $entity->getCep(),
            $entity->getLogradouro(),
            $entity->getNumero(),
            $entity->getComplemento(),
            $entity->getBairro(),
            $entity->getCidade(),
            $entity->getEstado(),
            $entity->isPrincipal()
        );
    }
}
